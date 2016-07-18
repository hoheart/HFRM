<?php

namespace Framework\Swoole;

/**
 * 处理异步事件的伺服者。
 * 他其实什么事情也不干，就是等待所有异步时间的完成，只要所有异步事件完成后，他也会从wait函数返回。
 *
 * @author Hoheart
 *        
 */
class AsyncWaiter {
	
	/**
	 *
	 * @var \EvLoop $mEv
	 */
	protected $mEvLoop = null;
	
	/**
	 * 事件总数
	 *
	 * @var int $mEventCount
	 */
	protected $mEventCount = 0;
	
	/**
	 * 已经触发的数
	 *
	 * @var integer
	 */
	protected $mTriggerdCount = 0;

	public function __construct () {
		$this->mEvLoop = new \EvLoop();
	}

	public function addEvent (\Closure &$fn) {
		$oldFn = $fn;
		$fn = function  () use( $oldFn) {
			++ $this->mTriggerdCount;
			
			$oldFn();
			
			if ($this->mTriggerdCount == $this->mEventCount) {
				$this->mEvLoop->stop();
			}
		};
		
		$io = $ev->io($fp, Ev::READ, $fn);
	}

	public function onEvent ($fp) {
		echo fread($fp, 1);
	}

	public function wait () {
		$this->mEvLoop->run();
	}
}