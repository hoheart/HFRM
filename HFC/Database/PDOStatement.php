<?php

namespace HFC\Database;

class PDOStatement extends DatabaseStatement {
	
	/**
	 *
	 * @var int
	 */
	const PARAM_INT = 1; // \PDO::PARAM_INT;
	const PARAM_STR = 2; // \PDO::PARAM_STR;
	
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

	public function fetchAll ($fetchStyle = DatabaseStatment::FETCH_BOTH) {
		return $this->mStatement->fetchAll($fetchStyle);
	}

	public function bindParam ($parameter, &$variable, $dataType = self::PARAM_STR, $length = 0, $options = null) {
		return $this->mStatement->bindParam($parameter, $variable, $dataType, $length, $options);
	}

	public function errorInfo () {
		return $this->mStatement->errorInfo();
	}
}
?>