<?php

namespace Framework;

use Framework\Module\ModuleManager;
use Framework\IService;
use Framework\Exception\ConfigErrorException;

/**
 * 服务管理器，可以在主进程里初始化服务池。
 *
 * @author Hoheart
 *        
 */
class ServiceManager {
	
	/**
	 *
	 * @var array
	 */
	protected $mServiceMap = array();

	public function __construct () {
	}

	public function get ($name, $caller = null) {
		if (null == $caller) {
			list ($caller, $callerModuleName) = App::GetCallerModule();
		}
		
		return $this->getService($name, $caller);
	}

	public function stop () {
		foreach ($this->mServiceMap as $service) {
			$service->stop();
		}
	}

	public function getService ($name, $caller = null) {
		if (null == $caller) {
			list ($caller, $callerModuleName) = App::GetCallerModule();
		}
		
		$keyName = $this->getKeyName($name, $caller);
		if (array_key_exists($keyName, $this->mServiceMap)) {
			$s = $this->mServiceMap[$keyName];
			
			return $s;
		}
		
		$s = $this->createService($name, $caller);
		
		$this->add2Map($keyName, $s);
		
		return $s;
	}

	protected function getKeyName ($name, $caller) {
		return $caller . '.' . $name;
	}

	protected function add2Map ($keyName, $s) {
		$this->mServiceMap[$keyName] = $s;
	}

	protected function createService ($name, $caller) {
		$conf = Config::Instance()->getModuleConfig($caller, 'service.' . $name);
		if (empty($conf)) {
			return null;
		}
		
		$clsName = $conf['class'];
		$method = empty($conf['method']) ? null : $conf['method'];
		$serviceConf = $conf['config'];
		if (null === $serviceConf) {
			$serviceConf = array();
		}
		$moduleAlias = $conf['module'];
		if (empty($moduleAlias)) {
			return null;
		}
		
		$s = null;
		ModuleManager::Instance()->preloadModule($conf['module']);
		if (! empty($method)) {
			$factory = new $clsName();
			$s = $factory->$method($serviceConf);
		} else {
			$s = new $clsName();
		}
		$s->init($serviceConf);
		$s->start();
		
		if (! $s instanceof IService) {
			throw new ConfigErrorException('the service is not a implementation of IService: service:' . $name);
		}
		
		return $s;
	}
}