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
		$this->releaseAll();
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
		$foundObj = null;
		$foundLocker = null;
		$index = - 1;
		
		foreach ($this->mLockerArray as $index => $locker) {
			if (! $locker->trylock()) {
				continue;
			}
			
			$obj = $this->mObjectArray[$index];
			$this->mLabelArray[$index] = true;
			
			$foundObj = $obj;
			$foundLocker = $locker;
		}
		
		if (null == $foundObj) {
			// 如果所有循环都完了还没有拿到锁，根据当前时间取一个等待
			$indx = (microtime(true) * 1000000) % $this->mLockerArray->getSize();
			$this->mLockerArray[$indx]->lock();
			$obj = $this->mObjectArray[$indx];
			$this->mLabelArray[$indx] = true;
			
			$foundObj = $obj;
			$foundLocker = $this->mLockerArray[$indx];
		}
		
		return array(
			$foundObj,
			$index
		);
	}

	public function release ($index) {
		if ($index >= 0 && $index < count($this->mLockerArray)) {
			$this->mLockerArray[$index]->unlock();
		}
	}

	public function releaseAll () {
		foreach ($this->mLockerArray as $locker) {
			$locker->unlock();
		}
	}
}