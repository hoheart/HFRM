<?php

namespace Framework\Swoole;

use HFC\Database\DatabaseClient;
use Framework\Exception\NotImplementedException;
use HFC\Database\DatabaseQueryException;

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
		try {
			$ret = call_user_func_array(array(
				$this->mOriginObj,
				$name
			), $arguments);
		} catch (DatabaseQueryException $e) {
			$se = $e->getSourceException();
			if ($se->errorInfo[1] == 2006 || $se->errorInfo[1] == 70100) {
				if ($this->mOriginObj->connect()) {
					$ret = call_user_func_array(array(
						$this->mOriginObj,
						$name
					), $arguments);
					
					Server::Instance()->needExit(Server::ERRCODE_DB_RECONNECT);
				}
			}
		}
		
		return $ret;
	}

	public function start () {
		list ($obj, $index) = $this->mPool->get();
		$this->mOriginObj = $obj;
		$this->mOriginIndex = $index;
		
		if (! $this->mOriginObj->isConnect()) {
			$this->mOriginObj->connect();
		}
		
		$this->mOriginObj->start();
	}

	public function stop ($normal = true) {
		$this->mOriginObj->stop();
		
		$this->mPool->release($this->mOriginIndex);
	}

	public function connect () {
		// 该函数不能直接被调用，在调用该函数之前就连接好。
		throw new NotImplementedException();
	}

	public function isConnect () {
		return $this->__call(__FUNCTION__, func_get_args());
	}

	public function exec ($sql) {
		return $this->__call(__FUNCTION__, func_get_args());
	}

	public function select ($sql, $inputParams = array(), $start = 0, $size = self::MAX_ROW_COUNT, $isOrm = false) {
		return $this->__call(__FUNCTION__, func_get_args());
	}

	public function query ($sql, array $inputParams = array()) {
		return $this->__call(__FUNCTION__, func_get_args());
	}

	public function transLimitSelect ($sql, $start, $size) {
		return $this->__call(__FUNCTION__, func_get_args());
	}

	public function beginTransaction () {
		return $this->__call(__FUNCTION__, func_get_args());
	}

	/**
	 * 回滚一个事务。
	 */
	public function rollBack () {
		return $this->__call(__FUNCTION__, func_get_args());
	}

	/**
	 * 提交一个事务。
	 */
	public function commit () {
		return $this->__call(__FUNCTION__, func_get_args());
	}

	public function inTransaction () {
		return $this->__call(__FUNCTION__, func_get_args());
	}

	public function change2SqlValue ($str, $type = 'string') {
		return $this->__call(__FUNCTION__, func_get_args());
	}
}