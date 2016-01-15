<?php

namespace HFC\Database;

class DatabaseTransaction {
	
	/**
	 *
	 * @var IDatabaseClient
	 */
	protected $mDatabaseClient = null;

	public function __construct (DatabaseClient $databaseClient) {
		$this->mDatabaseClient = $databaseClient;
		
		$this->mDatabaseClient->beginTransaction();
	}

	public function __destruct () {
		if ($this->inTransaction()) {
			$this->rollback();
		}
	}

	/**
	 * 回滚一个事务
	 */
	public function rollback () {
		return $this->mDatabaseClient->rollBack();
	}

	/**
	 * 提交一个事务
	 */
	public function commit () {
		return $this->mDatabaseClient->commit();
	}

	/**
	 * 判断事务是否还在执行中。
	 */
	public function inTransaction () {
		return $this->mDatabaseClient->inTransaction();
	}
}