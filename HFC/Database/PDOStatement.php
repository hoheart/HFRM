<?php

namespace Framework\HFC\Database;

class PDOStatement extends DatabaseStatement {
	
	/**
	 *
	 * @var int
	 */
	const PARAM_INT = \PDO::PARAM_INT;
	const PARAM_STR = \PDO::PARAM_STR;
	
	/**
	 * 对应DBMS的语句对象。
	 *
	 * @var \PDOStatement
	 */
	protected $mStatement = null;
	
	/**
	 *
	 * @var DatabaseClient
	 */
	protected $mClient = null;

	public function __construct (DatabaseClient $client, $statement = null) {
		$this->mStatement = $statement;
		$this->mClient = $client;
	}

	public function __destruct () {
		$this->closeCursor();
	}

	public function closeCursor () {
		if (false === $this->mStatement->closeCursor()) {
			$this->throwError();
		}
	}

	public function rowCount () {
		return $this->mStatement->rowCount();
	}

	public function fetch ($fetchStyle = self::FETCH_ASSOC, $cursorOrientation = self::FETCH_ORI_NEXT, $cursorOffset = 0) {
		return $this->mStatement->fetch($fetchStyle, $cursorOrientation, $cursorOffset);
	}

	/**
	 * 获取插入的自增长ID。
	 */
	public function lastInsertId () {
		return $this->mClient->lastInsertId();
	}

	protected function throwError () {
		$info = $this->mStatement->errorInfo();
		throw new DatabaseQueryException(
				'On statement operation Error: errorCode:' . $info[1] . ',errorMessage:' . $info[2] . '.');
	}

	public function execute (array $params = null) {
		return $this->mStatement->execute($params);
	}

	public function fetchAll ($fetchStyle = self::FETCH_BOTH) {
		return $this->mStatement->fetchAll($fetchStyle);
	}

	public function bindParam ($parameter, $variable, $dataType = self::PARAM_STR) {
		return $this->mStatement->bindParam($parameter, $variable, $dataType);
	}

	public function errorInfo () {
		return $this->mStatement->errorInfo();
	}
}