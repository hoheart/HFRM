<?php

namespace HFC\Database;

use HFC\Exception\SQLInjectionRiskException;

/**
 *
 * @author Hoheart
 *        
 */
abstract class PDOClient extends DatabaseClient {
	
	/**
	 * 这对不同DBMS的数据库客户端
	 *
	 * @var \PDO
	 */
	private $mClient = null;
	
	/**
	 * 对于数据库的配置
	 *
	 * @var array
	 */
	protected $mConf = null;

	public function exec ($sql) {
		try {
			$ret = $this->getClient()->exec($sql);
		} catch (\Exception $e) {
			$this->throwError($sql, null, $e);
		}
		
		if (false === $ret) {
			$this->throwError($sql);
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

	public function query ($sql) {
		$stmt = $this->getClient()->query($sql);
		
		return new PDOStatement($this, $stmt);
	}

	public function select ($sql, $inputParams = array(), $start = 0, $size = self::MAX_ROW_COUNT, $isOrm = false) {
		$this->checkSql($sql, $isOrm);
		
		$sql = $this->transLimitSelect($sql, $start, $size);
		
		try {
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
			
			$stmt = $this->prepare($sql, self::CURSOR_FWDONLY, $isOrm);
			
			if (is_array($inputParams)) {
				foreach ($inputParams as $key => $val) {
					$type = is_int($val) ? PDOStatement::PARAM_INT : PDOStatement::PARAM_STR;
					$stmt->bindParam($key, $val, $type);
				}
			}
			
			$stmt->execute();
			
			$ret = $stmt->fetchAll(DatabaseStatement::FETCH_ASSOC);
			if (false === $stmt->closeCursor()) {
				$this->throwError($sql, $stmt);
			}
		} catch (\Exception $e) {
			$this->throwError($sql, $stmt, $e);
		}
		
		return $ret;
	}

	public function prepare ($sql, $cursorType = self::CURSOR_FWDONLY, $isOrm) {
		try {
			$stmt = $this->getClient()->prepare($sql, array(
				self::ATTR_CURSOR => $cursorType
			));
			if (false === $stmt) {
				$this->throwError($sql);
			}
		} catch (\Exception $e) {
			$this->throwError($sql, $stmt, $e);
		}
		
		return new PDOStatement($this, $stmt);
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
		if (false === $this->getClient()->beginTransaction()) {
			$this->throwError('begin transaction.');
		}
	}

	public function rollBack () {
		if (false === $this->getClient()->rollBack()) {
			$this->throwError('roll back transaction.');
		}
	}

	public function commit () {
		if (false === $this->getClient()->commit()) {
			$this->throwError('commit transaction.');
		}
	}

	public function inTransaction () {
		return $this->getClient()->inTransaction();
	}

	public function lastInsertId () {
		return $this->getClient()->lastInsertId();
	}

	abstract protected function getDSN ();

	protected function getClient () {
		if (null != $this->mClient) {
			return $this->mClient;
		}
		
		$dsn = $this->getDSN();
		try {
			$this->mClient = new \PDO($dsn, $this->mConf['user'], $this->mConf['password']);
		} catch (\Exception $e) {
			throw new DatabaseConnectException('On Connection Error.' . $e->getMessage());
		}
		
		$this->mClient->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->mClient->setAttribute(\PDO::ATTR_AUTOCOMMIT, $this->mAutocommit);
		$this->mClient->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
		$this->mClient->setAttribute(\PDO::ATTR_TIMEOUT, 86400); // 超时时间设置为1天
		
		return $this->mClient;
	}

	public function connect () {
		return $this->getClient();
	}

	public function isConnect () {
		return null == $this->mClient;
	}

	protected function throwError ($sql, $stmt = null, \Exception $originalException = null) {
		$originalMsg = '';
		$sourceCode = 0;
		if (null != $originalException) {
			$originalMsg = '<br>The original message is: ' . $originalException->getMessage();
			$sourceCode = $originalException->getCode();
			if (0 == $sourceCode) {
				$sourceCode = $this->getClient()->errorCode();
			}
		} else {
			$obj = null == $stmt ? $this->getClient() : $stmt;
			$info = $obj->errorInfo();
			$sourceCode = $info[0];
		}
		$e = new DatabaseQueryException(
				'On execute SQL Error: errorCode:' . $info[1] . ',errorMessage:' . $info[2] . '. SQL: ' . $sql .
						 $originalMsg);
		$e->setSourceCode($sourceCode);
		throw $e;
	}
}