<?php

namespace Framework;

use HFC\Util\ArrayUtil;

/**
 * 对配置文件进行管理
 * 配置文件分为模块和全局两种，全局的配置文件放在与Framework同级的Config目录下，模块的配置文件放在模块根目录的Config目录下。
 *
 * @author Hoheart
 *        
 */
class Config {
	
	/**
	 *
	 * @var 模块对应的文件里的数组内容。
	 */
	protected $mConfMap = array();
	
	/**
	 * 本地需要替换的配置
	 *
	 * @var array
	 */
	protected $mLocalConfMap = array();

	/**
	 *
	 * @return \Framework\Config
	 */
	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new self();
		}
		
		return $me;
	}

	/**
	 * 如果是Framework或HFC调用，直接取全局的app.php的配置；
	 * 如果是模块内部调用，先取模块的配置，如果没有取到，再取全局app.php的配置。
	 *
	 * @param string $path        	
	 * @return mixed
	 */
	public function get ($path) {
		list ($callerAlias, $callerName) = App::GetCallerModule();
		if ('framework' == $callerAlias) {
			$callerAlias = '';
		}
		
		return $this->getModuleConfig($callerAlias, $path);
	}

	/**
	 * 把一个file include进来
	 *
	 * @param string $path
	 *        	可以是绝对路径，也可以是app根目录的相对路径。
	 * @return
	 *
	 */
	public function loadFile ($path) {
		$absPath = App::$ROOT_DIR . $path;
		if (file_exists($absPath)) {
			return include_once $absPath;
		}
		
		return null;
	}

	protected function parsePath ($moduleAlias, $path) {
		$pos = strpos($path, '.');
		if (false === $pos) {
			$fileName = $path;
			$pos = strlen($path);
		} else {
			$fileName = substr($path, 0, $pos);
		}
		$confKeyName = $moduleAlias . '.' . $fileName;
		
		return array(
			$confKeyName,
			$fileName,
			substr($path, $pos + 1)
		);
	}

	/**
	 * 用.localenv.php里的值替换配置
	 *
	 * @param array $conf        	
	 */
	protected function replaceLocalenv (&$conf, $moduleAlias, $fileName) {
		if (empty($this->mLocalConfMap)) {
			$localConfPath = '.localenv.php';
			if (file_exists($localConfPath)) {
				$this->mLocalConfMap = $this->loadFile($localConfPath);
			}
		}
		$localenvKey = $moduleAlias . '.' . $fileName;
		if (is_array($conf)) {
			if (array_key_exists($localenvKey, $this->mLocalConfMap)) {
				$conf = array_replace_recursive($conf, $this->mLocalConfMap[$localenvKey]);
			}
		}
		
		return $conf;
	}

	protected function getModuleConfigOnly ($moduleAlias, $path, $pathParsedRet = null) {
		if (null == $pathParsedRet) {
			list ($confKeyName, $fileName, $confPath) = $this->parsePath($moduleAlias, $path);
		} else {
			list ($confKeyName, $fileName, $confPath) = $pathParsedRet;
		}
		
		$conf = array();
		
		if (array_key_exists($confKeyName, $this->mConfMap)) {
			$conf = $this->mConfMap[$confKeyName];
		} else {
			$configFileDir = '';
			if ('framework' == $moduleAlias) {
				$configFileDir = 'Framework' . DIRECTORY_SEPARATOR;
			} else if (! empty($moduleAlias)) {
				$configFileDir = $this->getModuleConfig('framework', 'module.' . $moduleAlias . '.path');
			}
			
			$configFilePath = $configFileDir . 'Config' . DIRECTORY_SEPARATOR . $fileName . '.php';
			$conf = $this->loadFile($configFilePath);
			
			$this->replaceLocalenv($conf, $moduleAlias, $fileName);
			
			$this->mConfMap[$confKeyName] = $conf;
		}
		
		$ret = ArrayUtil::GetValueByPath($conf, $confPath);
		
		return $ret;
	}

	/**
	 * 取得模块的配置文件，如果模块没有配置要取得的项目，从全局配置文件中获取
	 *
	 * @param string $moduleAlias        	
	 * @param string $path        	
	 * @return Ambigous
	 */
	public function getModuleConfig ($moduleAlias, $path) {
		$pathParsedRet = $this->parsePath($moduleAlias, $path);
		
		$ret = $this->getModuleConfigOnly($moduleAlias, $path, $pathParsedRet);
		
		// 如果根本没有定义，就取全局的配置文件
		list ($confKeyName, $fileName, $confPath) = $pathParsedRet;
		if (null === $ret) {
			if (array_key_exists($fileName, $this->mConfMap)) {
				$ret = ArrayUtil::getValueByPath($this->mConfMap[$fileName], $confPath);
			} else {
				if ('' !== $moduleAlias) {
					$ret = $this->getModuleConfig('', $path);
				}
			}
		}
		
		return $ret;
	}
}