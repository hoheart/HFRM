<?php

namespace Framework\HFC\Database\Mysql;

use Framework\HFC\Database\DatabaseStatement;
use Framework\HFC\Database\DatabaseClient;

class MysqlStatementMysqli extends DatabaseStatement {
	
	/**
	 *
	 * @var \mysqli_stmt
	 */
	protected $mStmt = null;

	public function __construct (DatabaseClient $client, \mysqli_stmt $stmt = null) {
		parent::__construct($client);
		
		$this->mStmt = $stmt;
	}

	public function rowCount () {
		return $this->mStmt->num_rows();
	}

	public function fetch ($fetchStyle = self::FETCH_ASSOC, $cursorOrientation = self::FETCH_ORI_NEXT, $cursorOffset = 0) {
		$mysqliRet = $this->mStmt->get_result();
		$ret = null;
		switch ($fetchStyle) {
			case self::FETCH_ASSOC:
				$ret = $mysqliRet->fetch_assoc();
				break;
			case self::FETCH_BOTH:
				$ret = $mysqliRet->fetch_array();
				break;
			case self::FETCH_NUM:
				$ret = $mysqliRet->fetch_array(MYSQLI_NUM);
				break;
		}
		
		return $ret;
	}

	public function lastInsertId () {
		return $this->mStmt->insert_id;
	}

	public function execute (array $params = array()) {
		return $this->mStmt->execute();
	}

	public function fetchAll ($fetchStyle = self::FETCH_BOTH) {
		return $this->mStmt->get_result()->fetch_all($fetchStyle);
	}
}