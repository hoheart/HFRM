<?php

namespace hfc\database;

use hfc\exception\NotImplementedException;

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

	public function __construct (array $conf) {
		$this->mConf = $conf;
	}

	public function exec ($sql) {
		try {
			$ret = $this->getClient()->exec($sql);
			if (false === $ret) {
				$this->throwError($sql);
			}
		} catch (\Exception $e) {
			$this->throwError($sql);
		}
		
		return $ret;
	}

	public function select ($sql, $start = 0, $size = self::MAX_ROW_COUNT) {
		$sql = $this->transLimitSelect($sql, $start, $size);
		
		try {
			$stmt = $this->getClient()->query($sql);
			if (false === $stmt) {
				$this->throwError($sql);
			}
			$ret = $stmt->fetchAll(DatabaseStatement::FETCH_ASSOC);
			if (false === $stmt->closeCursor()) {
				$this->throwError($sql, $stmt);
			}
		} catch (\Exception $e) {
			$this->throwError($sql, $stmt, $e);
		}
		
		return $ret;
	}

	public function query ($sql, $cursorType = self::CURSOR_FWDONLY) {
		try {
			$stmt = $this->getClient()->prepare($sql, array(
				self::ATTR_CURSOR => $cursorType
			));
			if (false === $stmt) {
				$this->throwError($sql);
			}
			if (false === $stmt->execute()) {
				$this->throwError($sql, $stmt);
			}
		} catch (\Exception $e) {
			$this->throwError($sql, $stmt);
		}
		
		return new PDOStatement($this, $stmt);
	}

	public function transLimitSelect ($sql, $start, $size) {
		$commonLimitSql = " LIMIT $start , $size ";
		
		$unionPos = stripos($sql, 'union');
		if (false === $unionPos) {
			$sql .= $commonLimitSql;
		} else {
			$sql = "SELECT * FROM ($sql) $commonLimitSql";
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
		if (false === $this->getClient()->inTransaction()) {
			$this->throwError('inTransaction.');
		}
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
		
		return $this->mClient;
	}

	protected function throwError ($sql, $stmt = null,\Exception $e = null) {
		$obj = null == $stmt ? $this->getClient() : $stmt;
		$info = $obj->errorInfo();
		$originalMsg = '';
		if (null != $e) {
			$originalMsg = '<br>The original message is: ' . $e->getMessage();
		}
		throw new DatabaseQueryException(
				'On execute SQL Error: errorCode:' . $info[1] . ',errorMessage:' . $info[2] . '. SQL: ' . $sql .
						 $originalMsg);
	}

	public function change2SqlValue ($str, $type = 'string') {
		throw new NotImplementedException('this method must be Override.');
	}
}
?>