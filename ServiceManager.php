<?php

namespace Framework;

use Framework\Module\ModuleManager;
use Framework\IService;
use Framework\Exception\ConfigErrorException;
use Framework\Swoole\ObjectPool;

/**
 * 服务管理器，可以在主进程里初始化服务池。
 *
 * @author Hoheart
 *        
 */
class ServiceManager {
	
	/**
	 * 默认的连接数
	 *
	 * @var int
	 */
	const DEFAULT_CONNECTIONS_NUM = 5;
	
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
			
			if ($service instanceof ObjectPool) {
				$service->release();
			}
		}
	}

	public function initPoolService ($name) {
		$moduleAliasArr = ModuleManager::Instance()->getAllModuleAlias();
		foreach ($moduleAliasArr as $alias) {
			$conf = Config::Instance()->getModuleConfig($alias, 'service.' . $name);
			if (empty($conf)) {
				continue;
			}
			$num = $conf['connections_num'];
			if (empty($num)) {
				$num = self::DEFAULT_CONNECTIONS_NUM;
			}
			
			$pool = new ObjectPool();
			$pool->init(array(
				'num' => $num
			));
			
			for ($i = 0; $i < $num; ++ $i) {
				$s = $this->createService($name, $alias);
				$pool->addObject($i, $s);
			}
			
			$keyName = $this->getKeyName($name, $alias);
			$this->add2Map($keyName, $pool);
		}
	}

	public function getService ($name, $caller = null) {
		if (null == $caller) {
			list ($caller, $callerModuleName) = App::GetCallerModule();
		}
		
		$keyName = $this->getKeyName($name, $caller);
		if (array_key_exists($keyName, $this->mServiceMap)) {
			$s = $this->mServiceMap[$keyName];
			if ($s instanceof ObjectPool) {
				$s = $s->get();
			}
			
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