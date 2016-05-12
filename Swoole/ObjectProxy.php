<?php

namespace Framework\Swoole;

use Framework\IService;

class ObjectProxy implements IService {
	
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

	public function init (array $conf) {
	}

	public function start () {
	}

	public function stop ($normal = true) {
	}

	public function __call ($name, $arguments) {
		list ($obj, $index) = $this->mPool->get();
		$this->mOriginObj = $obj;
		$this->mOriginIndex = $index;
		
		$ret = call_user_func_array(array(
			$obj,
			$name
		), $arguments);
		
		$this->mPool->release($index);
		
		return $ret;
	}
}