<?php

namespace Framework\Module;

use Framework\Config;
use Framework\Exception\ModuleNotAvailableException;

class ModuleManager {
	
	/**
	 * 用模块别名索引的配置，其实就是配置文件配置的东西。
	 *
	 * @var array
	 */
	protected $mAliasIndexedConfig = array();
	
	/**
	 * 模块的服务的map
	 *
	 * @var array
	 */
	protected $mServiceMap = array();

	protected function __construct () {
		$this->mAliasIndexedConfig = Config::Instance()->get('module');
	}

	/**
	 *
	 * @return ModuleManager
	 */
	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$cls = get_called_class();
			$me = new $cls();
		}
		
		return $me;
	}

	public function isModuleEnable ($alias) {
		if (empty($alias)) {
			return false;
		}
		
		if (! array_key_exists($alias, $this->mAliasIndexedConfig)) {
			return false;
		}
		
		return $this->mAliasIndexedConfig[$alias]['enable'];
	}

	public function getService ($alias, $apiName) {
		$oneModule = $this->mServiceMap[$alias];
		if (! empty($oneModule)) {
			$s = $oneModule[$apiName];
			if (! empty($s)) {
				return $s;
			}
		}
		
		if (! $this->isModuleEnable($alias)) {
			throw new ModuleNotAvailableException('module not enable:' . $alias);
		}
		
		$path = $this->mAliasIndexedConfig[$alias]['path'];
		
		$s = new ServiceAgent($path, $apiName);
		$this->mServiceMap[$alias][$apiName] = $s;
		
		return $s;
	}

	public function waitAllResponse (array $httpClientList) {
	}
}