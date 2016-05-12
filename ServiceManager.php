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
class ServiceManager implements IService {
	
	/**
	 *
	 * @var array
	 */
	protected $mServiceMap = array();
	
	/**
	 *
	 * @var true $mStoped
	 */
	protected $mStoped = true;

	public function __construct () {
	}

	public function init (array $conf = array()) {
	}

	public function start () {
		if ($this->mStoped) {
			foreach ($this->mServiceMap as $service) {
				$service->start();
			}
			
			$this->mStoped = false;
		}
	}

	public function stop ($normal = true) {
		if (! $this->mStoped) {
			foreach ($this->mServiceMap as $service) {
				$service->stop($normal);
			}
			
			$this->mStoped = true;
		}
	}

	public function get ($name, $caller = null) {
		if (null == $caller) {
			list ($caller, $callerModuleName) = App::GetCallerModule();
		}
		
		return $this->getService($name, $caller);
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
		if (empty($s)) {
			return $s;
		}
		
		$this->addService($s, $keyName);
		
		return $s;
	}

	public function getKeyName ($name, $caller) {
		return $caller . '.' . $name;
	}

	public function addService (IService $s, $keyName) {
		unset($this->mServiceMap[$keyName]);
		$this->mServiceMap[$keyName] = $s;
	}

	public function createService ($name, $caller) {
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
		if (! $s instanceof IService) {
			throw new ConfigErrorException('the service is not a implementation of IService: service:' . $name);
		}
		
		$s->init($serviceConf);
		
		return $s;
	}
}