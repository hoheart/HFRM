<?php

namespace Framework\Cache;

use Framework\IService;
use Framework\Facade\Config;

class Cache implements IService {
	
	/**
	 *
	 * @var \Redis
	 */
	private $redis;

	public function __construct () {
	}

	public function __destruct () {
		$this->redis->close();
	}

	/**
	 *
	 * @param string $host        	
	 * @param int $post        	
	 */
	public function init (array $conf = array()) {
		$this->redis = new \Redis();
		$redis = Config::get('cache.redis');
		
		$this->redis->connect($redis['host'], $redis['port']);
		return $this->redis;
	}

	public function start () {
	}

	public function stop ($normal = true) {
	}

	/**
	 * 设置值 构建一个字符串
	 *
	 * @param string $key
	 *        	KEY名称
	 * @param string $value
	 *        	设置值
	 * @param int $timeOut
	 *        	时间 0表示无过期时间
	 */
	public function set ($key, $value, $timeOut = 0) {
		$retRes = $this->redis->set($key, $value);
		if ($timeOut > 0) {
			$this->redis->expire($key, $timeOut);
		}
		return $retRes;
	}

	/**
	 * 如果不存在则设置
	 *
	 * @param
	 *        	$key
	 * @param
	 *        	$value
	 * @return bool
	 */
	public function setnx ($key, $value) {
		return $this->redis->setnx($key, $value);
	}

	/**
	 * 设置多个值
	 *
	 * @param array $keyArray
	 *        	KEY名称
	 * @param string|array $value
	 *        	获取得到的数据
	 * @param int $timeOut
	 *        	时间
	 */
	public function sets ($keyArray, $timeout) {
		if (is_array($keyArray)) {
			$retRes = $this->redis->mset($keyArray);
			if ($timeout > 0) {
				foreach ($keyArray as $key => $value) {
					$this->redis->expire($key, $timeout);
				}
			}
			return $retRes;
		} else {
			return "Call  " . __FUNCTION__ . " method  parameter  Error !";
		}
	}

	/**
	 * 通过key获取数据
	 *
	 * @param string $key
	 *        	KEY名称
	 */
	public function get ($key) {
		$result = $this->redis->get($key);
		return $result;
	}

	/**
	 * 同时获取多个值
	 *
	 * @param ayyay $keyArray
	 *        	获key数值
	 */
	public function gets ($keyArray) {
		if (is_array($keyArray)) {
			return $this->redis->mget($keyArray);
		} else {
			return "Call  " . __FUNCTION__ . " method  parameter  Error !";
		}
	}

	public function del ($key) {
		return $this->redis->delete($key);
	}

	/**
	 * 同时删除多个key数据
	 *
	 * @param array $keyArray
	 *        	KEY集合
	 */
	public function dels ($keyArray) {
		if (is_array($keyArray)) {
			return $this->redis->del($keyArray);
		} else {
			return "Call  " . __FUNCTION__ . " method  parameter  Error !";
		}
	}

	/**
	 * 数据自增
	 *
	 * @param string $key
	 *        	KEY名称
	 */
	public function increment ($key) {
		return $this->redis->incr($key);
	}

	/**
	 * 数据自减
	 *
	 * @param string $key
	 *        	KEY名称
	 */
	public function decrement ($key) {
		return $this->redis->decr($key);
	}

	/**
	 * 判断key是否存在
	 *
	 * @param string $key
	 *        	KEY名称
	 */
	public function isExists ($key) {
		return $this->redis->exists($key);
	}

	/**
	 * 重命名- 当且仅当newkey不存在时，将key改为newkey ，当newkey存在时候会报错哦RENAME
	 * 和 rename不一样，它是直接更新（存在的值也会直接更新）
	 *
	 * @param string $Key
	 *        	KEY名称
	 * @param string $newKey
	 *        	新key名称
	 */
	public function modifyKeyName ($key, $newKey) {
		return $this->redis->RENAMENX($key, $newKey);
	}

	/**
	 * 获取KEY存储的值类型
	 * none(key不存在) int(0) string(字符串) int(1) list(列表) int(3) set(集合) int(2)
	 * zset(有序集) int(4) hash(哈希表) int(5)
	 *
	 * @param string $key
	 *        	KEY名称
	 */
	public function getDataType ($key) {
		return $this->redis->type($key);
	}
	
	// /////////////////////////////////////////////////////set表操作////////////////////////////////////////////////////
	// sadd 增加元素,返回true,重复返回false
	public function sadd ($key, $val) {
		return $this->redis->sadd($key, $val);
	}
	
	// scard 返回当前set表元素个数
	public function scard ($key) {
		return $this->redis->scard($key);
	}
	
	// spop 弹出首元素
	public function spop ($key) {
		return $this->redis->spop($key);
	}
	
	// sismember 判断元素是否属于当前表
	public function sismember ($key, $val) {
		return $this->redis->sismember($key, $val);
	}

	public function expire ($key, $timeout) {
		return $this->redis->expire($key, $timeout);
	}
}
