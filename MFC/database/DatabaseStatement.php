<?php

namespace hfc\database;

abstract class DatabaseStatement {
	
	/**
	 * 控制下一行如何返回给调用者。
	 *
	 * @var integer
	 */
	const FETCH_ASSOC = \PDO::FETCH_ASSOC;
	const FETCH_NUM = \PDO::FETCH_NUM;
	const FETCH_BOTH = \PDO::FETCH_BOTH;
	
	/**
	 * 获取数据的位置类型。
	 *
	 * @var integer
	 */
	const FETCH_ORI_NEXT = \PDO::FETCH_ORI_NEXT;
	const FETCH_ORI_PRIOR = \PDO::FETCH_ORI_PRIOR;
	const FETCH_ORI_FIRST = \PDO::FETCH_ORI_FIRST;
	const FETCH_ORI_LAST = \PDO::FETCH_ORI_LAST;
	const FETCH_ORI_ABS = \PDO::FETCH_ORI_ABS;
	const FETCH_ORI_REL = \PDO::FETCH_ORI_REL;
	
	/**
	 *
	 * @var DatabaseClient
	 */
	protected $mClient = null;

	abstract public function __construct (DatabaseClient $client);

	public function __destruct () {
		$this->closeCursor();
	}

	abstract public function closeCursor ();

	abstract public function rowCount ();

	abstract public function fetch ($fetchStyle = self::FETCH_ASSOC, $cursorOrientation = self::FETCH_ORI_NEXT, $cursorOffset = 0);

	abstract public function lastInsertId ();
}
?>