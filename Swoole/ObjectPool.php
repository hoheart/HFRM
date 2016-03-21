<?php

namespace Framework\Swoole;

use Framework\IService;
use HFC\Database\DatabaseClientFactory;

class ObjectPool implements IService {
	
	/**
	 * 默认的连接数
	 *
	 * @var int
	 */
	const DEFAULT_CONNECTIONS_NUM = 5;
	
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

	public function __construct () {
	}

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new self();
		}
		
		return $me;
	}

	public function init ($conf) {
		$num = empty($conf['connections_num']) ? self::DEFAULT_CONNECTIONS_NUM : $conf['connections_num'];
		$this->mPool = new \SplFixedArray($num);
		
		$f = new DatabaseClientFactory();
		for ($i = 0; $i < $num; ++ $i) {
			$swooleDb = new DatabaseClient();
			$this->mPool[$i] = $swooleDb;
			
			$db = $f->create($conf);
			$swooleDb->setDb($db);
		}
	}

	public function start () {
		foreach ($this->mPool as $swooleDb) {
			$swooleDb->db->start();
		}
	}

	public function stop () {
		foreach ($this->mPool as $swooleDb) {
			$swooleDb->db->stop();
		}
	}

	/**
	 * 让每个进程获取db服务时，从Pool里取一个，并锁上，不让其他进程再取得。
	 */
	public function get () {
		foreach ($this->mPool as $swooleDb) {
			if (! $swooleDb->locker->trylock()) {
				continue;
			}
			
			return $swooleDb->getDb();
		}
		
		// 如果所有循环都完了还没有拿到锁，根据当前时间取一个等待
		$indx = (microtime(true) * 1000000) % $this->mPool->getSize();
		$firstSwooleDb = $this->mPool[$indx];
		$firstSwooleDb->locker->lock();
		return $firstSwooleDb->getDb();
	}
}