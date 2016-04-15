<?php

namespace Framework\Swoole;

class ObjectProxy {
	
	/**
	 *
	 * @var object
	 */
	protected $mOriginObj = null;
	
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
			$this->mPool->release($this->mOriginObj);
		}
	}

	public function __call ($name, $arguments) {
		$obj = $this->mPool->get();
		$this->mOriginObj = $obj;
		
		$ret = call_user_func_array(array(
			$obj,
			$name
		), $arguments);
		
		$this->mPool->release($obj);
		
		return $ret;
	}
}