<?php

namespace Framework\Module;

use Framework\Config;

/**
 * 通用的模块类
 *
 * @author Hoheart
 *        
 */
class CommonModule implements IModule {
	
	/**
	 * 模块的配置
	 *
	 * @var string
	 */
	protected $mAlias = '';
	protected $mModuleConf = null;
	
	/**
	 * 提供的服务列表
	 *
	 * @var array
	 */
	protected $mServiceMap = array();

	public function load ($alias, $moduleConf) {
		$this->mAlias = $alias;
		$this->mModuleConf = $moduleConf;
	}

	public function __destruct () {
		$this->release();
	}

	public function release () {
		while (! empty($this->mServiceMap)) {
			$s = array_pop($this->mServiceMap);
			$s->stop();
		}
	}

	public function getDesc () {
		return Config::Instance()->getModuleConfig($this->mAlias, 'app.moduleDesc');
	}

	public function getService ($className) {
		if (array_key_exists($className, $this->mServiceMap)) {
			return $this->mServiceMap[$className];
		}
		
		$s = new ServiceAgent();
		$s->init(
				array(
					'moduleAlias' => $this->mAlias,
					'modulePath' => $this->mModuleConf['path'],
					'className' => $className
				));
		$s->start();
		
		$this->mServiceMap[$className] = $s;
		
		return $s;
	}
}