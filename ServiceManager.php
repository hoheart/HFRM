<?php

namespace Framework;

use Framework\Module\ModuleManager;
use Framework\IService;
use Framework\Exception\ConfigErrorException;

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
		
		$keyName = $caller . '.' . $name;
		if (array_key_exists($keyName, $this->mServiceMap)) {
			return $this->mServiceMap[$keyName];
		}
		
		$conf = Config::Instance()->getModuleConfig($caller, 'service.' . $name);
		if (empty($conf)) {
			return null;
		}
		
		$clsName = $conf['class'];
		$method = $conf['method'];
		$serviceConf = $conf['config'];
		$moduleAlias = $conf['module'];
		if (empty($moduleAlias)) {
			return null;
		}
		
		$s = null;
                var_dump($conf['module']);
		ModuleManager::Instance()->preloadModule($conf['module']);
		if (! empty($method)) {
			$factory = new $clsName();
			$s = $factory->$method($serviceConf);
		} else {
			$s = new $clsName($serviceConf);
		}
		
		if (! $s instanceof IService) {
			throw new ConfigErrorException('the service is not a implementation of IService: service:' . $name);
		}
		
		$this->mServiceMap[$keyName] = $s;
		
		return $s;
	}
}