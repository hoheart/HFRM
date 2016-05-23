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
	
	/**
	 * 为防止事务被别的进程提交，当非select语句执行时，都是在一个事务中执行。
	 *
	 * @var bool $mIntransaction
	 */
	protected $mIntransaction = true;

	public function __construct (ObjectPool $pool) {
		$this->mPool = $pool;
	}

	public function __destruct () {
		if (null != $this->mOriginObj) {
			$this->mPool->release($this->mOriginIndex);
		}
	}

	public function __call ($name, $arguments) {
		$obj = null;
		$idx = - 1;
		if (null == $this->mOriginObj) {
			list ($obj, $idx) = $this->lock();
		} else {
			$obj = $this->mOriginObj;
			$idx = $this->mOriginIndex;
		}
		
		try {
			$ret = call_user_func_array(array(
				$obj,
				$name
			), $arguments);
		} catch (DatabaseQueryException $e) {
			$this->unlock($idx);
			
			$se = $e->getSourceException();
			if ($se->errorInfo[1] == 2006 || $se->errorInfo[1] == 70100) {
				if ($obj->connect()) {
					$ret = call_user_func_array(array(
						$obj,
						$name
					), $arguments);
					
					Server::Instance()->needExit(Server::ERRCODE_DB_RECONNECT);
				}
			} else {
				throw $e;
			}
		}
		
		if (! $this->mIntransaction) {
			$this->unlock($idx);
		}
		
		return $ret;
	}

	public function start () {
	}

	protected function lock () {
		$ret = $this->mPool->get();
		
		list ($this->mOriginObj, $this->mOriginIndex) = $ret;
		
		$this->mOriginObj->start();
		
		return $ret;
	}

	protected function unlock ($idx = -1) {
		if (- 1 == $idx) {
			if (- 1 == $this->mOriginIndex) {
				return;
			} else {
				$idx = $this->mOriginIndex;
			}
		}
		
		$this->mPool->release($idx);
		
		$this->mOriginObj = null;
		$this->mOriginIndex = - 1;
	}

	public function stop ($normal = true) {
		if (null != $this->mOriginObj) {
			$this->mOriginObj->stop($normal);
			
			$this->unlock();
		}
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
		if (null == $this->mOriginObj) {
			$this->mIntransaction = false;
		}
		
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