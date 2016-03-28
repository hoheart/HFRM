<?php

namespace Framework\Swoole;

use Framework\IService;

class ObjectPool implements IService {
	
	/**
	 *
	 * @var \SplFixedArray $mPool
	 */
	protected $mObjectArray = null;
	
	/**
	 *
	 * @var \SplFixedArray $mLockerArray
	 */
	protected $mLockerArray = null;
	
	/**
	 * 用于标记是否锁住了
	 *
	 * @var array $mLabelArray
	 */
	protected $mLabelArray = null;

	public function __construct () {
	}

	public function __destruct () {
		$this->release();
	}

	public function init (array $conf) {
		$num = $conf['num'];
		$this->mObjectArray = new \SplFixedArray($num);
		$this->mLockerArray = new \SplFixedArray($num);
		$this->mLabelArray = new \SplFixedArray($num);
	}

	public function start () {
	}

	public function stop () {
	}

	public function addObject ($i, $s) {
		$this->mObjectArray[$i] = $s;
		$this->mLockerArray[$i] = new \swoole_lock();
	}

	/**
	 * 让每个进程获取db服务时，从Pool里取一个，并锁上，不让其他进程再取得。
	 */
	public function get () {
		foreach ($this->mLockerArray as $key => $locker) {
			if (! $locker->trylock()) {
				continue;
			}
			
			$obj = $this->mObjectArray[$key];
			$this->mLabelArray[$key] = true;
			
			return $obj;
		}
		
		// 如果所有循环都完了还没有拿到锁，根据当前时间取一个等待
		$indx = (microtime(true) * 1000000) % $this->mLockerArray->getSize();
		$this->mLockerArray[$indx]->lock();
		$obj = $this->mObjectArray[$indx];
		$this->mLabelArray[$indx] = true;
		
		return $obj;
	}

	public function release () {
		foreach ($this->mLabelArray as $key => $isLock) {
			if ($isLock) {
				$this->mLockerArray[$key]->unlock();
				$this->mLabelArray[$key] = false;
			}
		}
	}
}