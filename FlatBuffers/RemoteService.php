<?php

namespace Framework;

use Google\FlatBuffers\FlatbufferBuilder;

class RemoteService implements IService {
	
	/**
	 *
	 * @var map
	 */
	protected static $mServiceMap;
	
	/**
	 *
	 * @var string $mUrl
	 */
	protected $mUrl = '';

	protected function __construct ($url) {
		$this->mUrl;
	}

	/**
	 *
	 * @param string $moduleAlias        	
	 * @param string $apiName        	
	 */
	static public function get ($moduleAlias, $apiName) {
		$key = $moduleAlias . $apiName;
		if (! array_key_exists($key, self::$mServiceMap)) {
			$s = new RemoteService();
			$url = Config::Instance()->get("module.$moduleAlias.$apiName.path");
			$s = new self($url);
			
			self::$mServiceMap[$key] = $s;
		}
		
		return $s;
	}

	protected function getFbs ($path, $apiName) {
		$url = $path . '/fbs?api=' . $apiName;
	}

	public function __call ($name, $arguments) {
		$builder = new FlatbufferBuilder(0);
		
		$cls = $weapon_one = $builder->createString("Sword");
	}

	public function init (array $conf) {
	}

	public function start () {
	}

	public function stop () {
	}
}