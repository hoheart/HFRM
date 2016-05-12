<?php

namespace Framework\Module;

use Framework\Config;
use Framework\Exception\ModuleNotAvailableException;
use Framework\Exception\ConfigErrorException;
use Framework\App;
use Framework\IService;

class ModuleManager implements IService {
	
	/**
	 * 用模块路径索引的模块配置
	 * 主要用与找调用者模块时。
	 *
	 * @var array
	 */
	protected $mModulePathIndexedConfig = null;
	
	/**
	 * 用模块名索引的模块配置
	 * 主要用于autoload时，根据类名寻找模块
	 *
	 * @var array
	 */
	protected $mModuleNameIndexedConfig = null;
	
	/**
	 * 用模块别名索引的配置，其实就是配置文件配置的东西。
	 *
	 * @var array
	 */
	protected $mAliasIndexedConfig = array();
	
	/**
	 * 模块的实例
	 *
	 * @var array
	 */
	protected $mModuleMap = array();

	protected function __construct () {
		$this->mAliasIndexedConfig = Config::Instance()->getModuleConfig('', 'module');
		
		$this->mModulePathIndexedConfig = array(
			'Framework' . DIRECTORY_SEPARATOR => array(
				'framework',
				array()
			),
			'HFC' . DIRECTORY_SEPARATOR => array(
				'HFC',
				array()
			)
		);
		
		$this->mModuleNameIndexedConfig = array(
			'Framework' => array(
				'framework' => array()
			),
			'HFC' => array(
				'HFC' => array()
			)
		);
	}

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new self();
		}
		
		return $me;
	}

	public function init (array $conf) {
	}

	public function start () {
	}

	public function stop ($normal = true) {
		while (! empty($this->mModuleMap)) {
			$m = array_pop($this->mModuleMap);
			$m->release();
		}
	}

	/**
	 * 预载入模块，为后续调用提供：判断是否为同一个模块的依据。
	 * 该方法一般只提供给框架调用Controller时，其他地方应该用不到。
	 *
	 * @param string $alias        	
	 * @throws ModuleNotAvailableException
	 */
	public function preloadModule ($alias) {
		if ('framework' == $alias || 'hfc' == $alias) {
			return;
		}
		
		if (! array_key_exists($alias, $this->mAliasIndexedConfig)) {
			throw new ModuleNotAvailableException('can not preload module:' . $alias);
		}
		
		$moduleConf = $this->mAliasIndexedConfig[$alias];
		
		$modulePath = $this->mAliasIndexedConfig[$alias]['path'];
		$this->mModulePathIndexedConfig[$modulePath] = array(
			$alias,
			$moduleConf
		);
		$this->mModuleNameIndexedConfig[$moduleConf['name']][$alias] = $moduleConf;
	}

	public function getLoadedModuleAliasByPath ($path) {
		if (array_key_exists($path, $this->mModulePathIndexedConfig)) {
			return $this->mModulePathIndexedConfig[$path];
		}
		
		return array(
			'',
			''
		);
	}

	public function getLoadedModuleAliasByName ($moduleName) {
		if (array_key_exists($moduleName, $this->mModuleNameIndexedConfig)) {
			return $this->mModuleNameIndexedConfig[$moduleName];
		}
		
		return array();
	}

	public function getModulePath ($alias) {
		$aliasIndex = Config::Instance()->get('module');
		if (! array_key_exists($alias, $aliasIndex)) {
			throw new ModuleNotAvailableException('can not get module path. module:' . $alias);
		}
		
		return $aliasIndex[$alias]['path'];
	}

	public function isModuleEnable ($alias) {
		if (empty($alias)) {
			return false;
		}
		
		if ('framework' == $alias) {
			return true;
		}
		
		if (! array_key_exists($alias, $this->mAliasIndexedConfig)) {
			return false;
		}
		
		return $this->mAliasIndexedConfig[$alias]['enable'];
	}

	public function getModuleName ($alias) {
		if ('framework' == $alias) {
			return 'Framework';
		}
		
		if (! array_key_exists($alias, $this->mAliasIndexedConfig)) {
			throw new ModuleNotAvailableException('module:' . $alias);
		}
		
		return $this->mAliasIndexedConfig[$alias]['name'];
	}

	public function get ($alias) {
		// 检查是否依赖这个模块了
		list ($callerAlias, $callerName) = App::GetCallerModule();
		if ('framework' != $callerAlias) {
			$dependsList = Config::Instance()->getModuleConfig($callerAlias, 'app.depends');
			if ($alias != $callerAlias && ! array_key_exists($alias, $dependsList)) {
				throw new ConfigErrorException('depends on `' . $alias . '` did not indicate in the config.');
			}
		}
		
		$m = null;
		if (array_key_exists($alias, $this->mModuleMap)) {
			$m = $this->mModuleMap[$alias];
		} else {
			$moduleConf = Config::Instance()->getModuleConfig($callerAlias, 'module.' . $alias);
			if (! $moduleConf['enable']) {
				throw new ModuleNotAvailableException('module not enable:' . $alias);
			}
			
			// 记录索引
			$this->mModulePathIndexedConfig[$moduleConf['path']] = array(
				$alias,
				$moduleConf
			);
			$this->mModuleNameIndexedConfig[$moduleConf['name']][$alias] = $moduleConf;
			
			$moduleClass = '';
			if (! array_key_exists('moduleClass', $moduleConf)) {
				$moduleClass = '\Framework\Module\CommonModule';
			} else {
				$moduleClass = $moduleConf['moduleClass'];
				
				$this->preloadModule($alias);
			}
			
			$m = new $moduleClass();
			$mdApiName = '\Framework\Module\IModule';
			if (! $m instanceof $mdApiName) {
				throw new ConfigErrorException('module class of the config is not implement Framework\Module\IModule.');
			}
			$m->load($alias, $moduleConf);
			
			$this->mModuleMap[$alias] = $m;
		}
		
		return $m;
	}

	public function getAllModuleAlias () {
		return array_keys($this->mAliasIndexedConfig);
	}
}