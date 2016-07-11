<?php

namespace Framework\HFC\Database\Mysql;

use Framework\HFC\Database\DatabaseClient;
use Framework\HFC\Database\DatabaseConnectException;
use Framework\HFC\Database\DatabaseQueryException;
use Framework\HFC\Exception\SQLInjectionRiskException;

/**
 * 用mysqli实现的mysql客户端
 * mysqli支持自动重连接
 *
 * @author Hoheart
 *        
 */
class MysqlClientMysqli extends DatabaseClient {
	
	/**
	 *
	 * @var boolean
	 */
	protected $mConnected = false;
	
	/**
	 *
	 * @var \mysqli $mMysqli
	 */
	protected $mMysqli = null;
	
	/**
	 * 是否已经在事物中了。
	 *
	 * @var boolean
	 */
	protected $mIntransaction = false;

	public function init (array $conf = array()) {
		$this->mMysqli = new \mysqli();
		
		$this->mMysqli->autocommit($this->mAutocommit);
		
		$this->mMysqli->set_charset($conf['charset']);
		
		parent::init($conf);
	}

	public function connect () {
		$ret = $this->mMysqli->real_connect($this->mConf['server'], $this->mConf['user'], $this->mConf['password'], 
				$this->mConf['name'], $this->mConf['port']);
		if (false === $ret) {
			throw new DatabaseConnectException('On Connection Error.' . $this->mMysqli->error);
		}
	}

	public function isConnect () {
		return $this->mMysqli->ping();
	}

	public function exec ($sql) {
		$ret = $this->mMysqli->query($sql);
		if (true === $ret) {
			return $this->mMysqli->affected_rows;
		} else {
			throw new DatabaseQueryException('SQL: ' . $sql);
		}
	}

	public function select ($sql, $inputParams = array(), $start = 0, $size = self::MAX_ROW_COUNT, $isOrm = false) {
		$this->checkSql($sql, $isOrm);
		
		$sql = $this->transLimitSelect($sql, $start, $size);
		
		// 由于不能为IN()语句绑定多个值，所以先过滤掉
		if (is_array($inputParams)) {
			foreach ($inputParams as $key => $val) {
				if (is_array($val)) {
					$sqlVal = $this->createArrayParams($val);
					$sql = str_replace($key, $sqlVal, $sql);
					
					unset($inputParams[$key]);
				}
			}
		}
		
		$stmt = $this->mMysqli->prepare($sql);
		if (false === $stmt) {
			$this->throwError($sql);
		}
		
		if (is_array($inputParams)) {
			$strType = '';
			foreach ($inputParams as $key => $val) {
				if (is_int($val)) {
					$strType .= 'i';
				} else {
					$strType .= 's';
				}
			}
			
			$stmt->bind_param($strType, $strType, $a);
		}
		
		if (false === $stmt->execute()) {
			$this->throwError($sql, $stmt);
		}
		
		$mysqliRet = $stmt->get_result();
		if (false === $mysqliRet) {
			$this->throwError($sql, $stmt);
		}
		$ret = $mysqliRet->fetch_all(MysqlStatement::FETCH_ASSOC);
		if (false === $ret) {
			$this->throwError($sql, $stmt);
		}
		
		return $ret;
	}

	protected function createArrayParams ($arr) {
		if (! is_array($arr)) {
			return $arr;
		}
		
		$sqlVal = '';
		$sqlArr = $arr;
		if (count($arr) >= 1) {
			if (! is_int($arr[0])) {
				$sqlArr = array();
				foreach ($arr as $val) {
					$sqlArr[] = $this->change2SqlValue($val);
				}
			}
			
			$sqlVal = '(' . implode(',', $sqlArr) . ')';
		}
		
		return $sqlVal;
	}

	/**
	 * 检查sql语句是否含有参数。
	 * 判断规则：如果操作符后面的字符是数字或者单引号，就抛出SQL有注入漏洞风险的异常。这种情况必须用冒号进行参数化。
	 * 支持的操作符：'=','IN','LIKE','>','<','<>','!='
	 *
	 * @param string $sql        	
	 * @param boolean $isOrm
	 *        	是否是orm在调用，因为orm有自己的sql检查规则，所以不用再检测
	 */
	protected function checkSql ($sql, $isOrm) { // return;
		if (! $this->mConf['debug'] || $isOrm) {
			return;
		}
		
		$matches = array();
		if (! preg_match_all('/(=|IN|>|!=|LIKE|<>|<) */im', $sql, $matches, PREG_OFFSET_CAPTURE)) {
			return;
		}
		
		foreach ($matches[0] as $one) {
			$posValue = $one[1] + strlen($one[0]);
			$value = substr($sql, $posValue, 1);
			if (':' !== $value && (is_numeric($value) || '\'' === $value || '-' === $value)) {
				throw new SQLInjectionRiskException('SQL: ' . $sql);
			}
		}
	}

	public function query ($sql, $cursorType = self::CURSOR_FWDONLY) {
		$stmt = $this->mMysqli->query($sql);
		
		return new MysqlStatement($this, $stmt);
	}

	public function transLimitSelect ($sql, $start, $size) {
		$commonLimitSql = " LIMIT $start , $size ";
		
		$unionPos = stripos($sql, 'union');
		if (false === $unionPos) {
			$sql .= $commonLimitSql;
		} else {
			$sql = "SELECT * FROM ($sql) t $commonLimitSql";
		}
		
		return $sql;
	}

	public function beginTransaction () {
		if (! $this->mMysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE)) {
			throw new DatabaseQueryException('can not beginTransaction.');
		}
		
		$this->mIntransaction = true;
	}

	public function rollBack () {
		if (! $this->mMysqli->rollback()) {
			throw new DatabaseQueryException('can not rollBack.');
		}
		
		$this->mIntransaction = false;
	}

	public function commit () {
		if (! $this->mMysqli->commit()) {
			throw new DatabaseQueryException('can not rollBack.');
		}
		
		$this->mIntransaction = false;
	}

	public function inTransaction () {
		return $this->mIntransaction;
	}

	public function change2SqlValue ($val, $type = 'string') {
		if (null === $val) {
			return 'null';
		}
		
		$type = strtolower($type);
		if ("\\" == $type[0]) {
			$type = substr($type, 1);
		}
		if (is_array($val)) {
			$ret = array();
			foreach ($val as $oneVal) {
				$ret[] = $this->change2SqlValue($oneVal, $type);
			}
			
			return $ret;
		}
		
		$v = null;
		if ('string' == substr($type, 0, 6)) {
			$v = $this->getClient()->quote($val);
		} else if ('int' == substr($type, 0, 3)) {
			$v = (int) $val;
		} else if ('float' == substr($type, 0, 5)) {
			$v = (float) $val;
		} else {
			switch ($type) {
				case 'date':
					$v = $val->format('Y-m-d');
					break;
				case 'time':
				case 'datetime':
					$v = $val->format('Y-m-d H:i:s');
					break;
				case 'boolean':
					$v = $val ? 1 : 0;
					break;
			}
			
			$v = "'$v'";
		}
		
		return $v;
	}

	protected function throwError ($sql, \mysqli_stmt $stmt = null) {
		$errcode = 0;
		$errstr = '';
		if (null == $stmt) {
			$errcode = $this->mMysqli->errno;
			$errstr = $this->mMysqli->error;
		} else {
			$errcode = $stmt->errno;
			$errstr = $stmt->error;
		}
		$e = new DatabaseQueryException(
				'On execute SQL Error: errorCode:' . $errcode . ',errorMessage:' . $errstr . '. SQL: ' . $sql);
		$e->setSourceCode($errcode);
		throw $e;
	}
}