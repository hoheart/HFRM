<?php

namespace HFC\Database;

abstract class DatabaseStatement {
	
	/**
	 * 控制下一行如何返回给调用者。
	 *
	 * @var integer
	 */
	const FETCH_ASSOC = 2; // \PDO::FETCH_ASSOC;
	const FETCH_NUM = 3; // \PDO::FETCH_NUM;
	const FETCH_BOTH = 4; // \PDO::FETCH_BOTH;
	
	/**
	 * 获取数据的位置类型。
	 *
	 * @var integer
	 */
	const FETCH_ORI_NEXT = 0; // \PDO::FETCH_ORI_NEXT;
	const FETCH_ORI_PRIOR = 1; // \PDO::FETCH_ORI_PRIOR;
	const FETCH_ORI_FIRST = 2; // \PDO::FETCH_ORI_FIRST;
	const FETCH_ORI_LAST = 3; // \PDO::FETCH_ORI_LAST;
	const FETCH_ORI_ABS = 4; // \PDO::FETCH_ORI_ABS;
	const FETCH_ORI_REL = 5; // \PDO::FETCH_ORI_REL;
	
	/**
	 *
	 * @var DatabaseClient
	 */
	protected $mClient = null;

	abstract public function __construct (DatabaseClient $client);

	public function __destruct () {
		$this->closeCursor();
	}

	abstract public function rowCount ();

	abstract public function fetch ($fetchStyle = self::FETCH_ASSOC, $cursorOrientation = self::FETCH_ORI_NEXT, $cursorOffset = 0);

	abstract public function lastInsertId ();

	abstract public function execute (array $params = array());

	abstract public function fetchAll ($fetchStyle = self::FETCH_BOTH);
}