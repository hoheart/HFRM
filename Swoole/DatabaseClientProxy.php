<?php

namespace Framework\Swoole;

use Framework\IService;

class DatabaseClientProxy {
	
	/**
	 * 源对象
	 *
	 * @var IService
	 */
	protected $mObj = null;
	
	/**
	 *
	 * @var \swoole_lock
	 */
	protected $mLocker = null;

	public function __construct ($obj, $locker) {
		$this->mObj = $obj;
		$this->mLocker = $locker;
	}

	public function __destruct () {
		$this->mLocker->unlock();
	}

	public function __call ($name, $arguments) {
		return call_user_func_array(array(
			$this->mObj,
			$name
		), $arguments);
	}

	/**
	 * 回滚一个事务。
	 */
	public function rollBack (){
		$this->mObj->rollBack();
		$this->mLocker->unlock();
	}

	/**
	 * 提交一个事务。
	 */
	public function commit ();
}