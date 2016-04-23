<?php

namespace Framework\Swoole;

use HFC\Database\DatabaseClient;
use Framework\Exception\NotImplementedException;

class DatabaseClientProxy extends DatabaseClient {
	
	/**
	 *
	 * @var object
	 */
	protected $mOriginObj = null;
	
	/**
	 *
	 * @var integer
	 */
	protected $mOriginIndex = - 1;
	
	/**
	 *
	 * @var ObjectPool
	 */
	protected $mPool = null;

	public function __construct (ObjectPool $pool) {
		$this->mPool = $pool;
	}

	public function __destruct () {
		if (null != $this->mOriginObj) {
			$this->mPool->release($this->mOriginIndex);
		}
	}

	public function __call ($name, $arguments) {
		$ret = call_user_func_array(array(
			$this->mOriginObj,
			$name
		), $arguments);
		
		return $ret;
	}

	public function connect () {
		// 该函数不能直接被调用，在调用该函数之前就连接好。
		throw new NotImplementedException();
	}

	public function exec ($sql) {
		return $this->mOriginObj->exec($sql);
	}

	public function transLimitSelect ($sql, $start, $size) {
		return $this->mOriginObj->transLimitSelect($sql, $start, $size);
	}

	public function change2SqlValue ($str, $type = 'string') {
		return $this->mOriginObj->change2SqlValue($str, $type);
	}

	public function query ($sql) {
		return $this->mOriginObj->query($sql);
	}

	public function isConnect () {
		return $this->mOriginObj->isConnect();
	}

	public function inTransaction () {
		return $this->mOriginObj->inTransaction();
	}

	public function select ($sql, $inputParams = array(), $start = 0, $size = self::MAX_ROW_COUNT, $isOrm = false) {
		return $this->mOriginObj->select($sql, $inputParams, $start, $size, $isOrm);
	}

	public function start () {
		$this->beginTransaction();
	}

	public function beginTransaction () {
		list ($obj, $index) = $this->mPool->get();
		$this->mOriginObj = $obj;
		$this->mOriginIndex = $index;
		
		if (! $this->mOriginObj->isConnect()) {
			$this->mOriginObj->connect();
		}
		
		$this->mOriginObj->beginTransaction();
	}

	/**
	 * 回滚一个事务。
	 */
	public function rollBack () {
		$this->mOriginObj->rollBack();
		$this->mPool->release($this->mOriginIndex);
		
		$this->beginTransaction();
	}

	/**
	 * 提交一个事务。
	 */
	public function commit () {
		$this->mOriginObj->commit();
		$this->mPool->release($this->mOriginIndex);
		
		$this->beginTransaction();
	}
}