<?php

namespace Framework;

use Framework\HFC\Util\ArrayUtil;

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
		$fileName = '';
		$pos = strpos($path, '.');
		if (false === $pos) {
			$fileName = $path;
			$pos = strlen($path);
		} else {
			$fileName = substr($path, 0, $pos);
		}
		
		$conf = array();
		
		if (array_key_exists($fileName, $this->mConfMap)) {
			$conf = $this->mConfMap[$fileName];
		} else {
			$moduleDir = '';
			if (! array_key_exists('app', $this->mConfMap)) {
				$this->loadConfApp();
			}
			$moduleDir = $this->mConfMap['app']['moduleDir'];
			$moduleConfPath = $moduleDir . 'Config' . DIRECTORY_SEPARATOR . $fileName . '.php';
			$moduleConf = $this->loadFile($moduleConfPath);
			if (null === $moduleConf) {
				$moduleConf = array();
			}
			
			$globalConfPath = 'Config' . DIRECTORY_SEPARATOR . $fileName . '.php';
			$globalConf = $this->loadFile($globalConfPath);
			if (null === $globalConf) {
				$globalConf = array();
			}
			
			$conf = array_replace_recursive($globalConf, $moduleConf);
			
			$conf = $this->replaceLocalenv($conf, $fileName);
			
			$this->mConfMap[$fileName] = $conf;
		}
		
		$confPath = substr($path, $pos + 1);
		$ret = ArrayUtil::GetValueByPath($conf, $confPath);
		
		return $ret;
	}

	/**
	 * 取得app.php里的配置
	 */
	protected function loadConfApp () {
		$confApp = $this->loadFile('Config' . DIRECTORY_SEPARATOR . 'app.php');
		$moduleDir = $confApp['moduleDir'];
		$confAppModule = $this->loadFile($moduleDir . 'Config' . DIRECTORY_SEPARATOR . 'app.php');
		if (null === $confAppModule) {
			$confAppModule = array();
		}
		
		$conf = array_replace_recursive($confApp, $confAppModule);
		
		$conf = $this->replaceLocalenv($conf, 'app');
		
		$this->mConfMap['app'] = $conf;
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

	/**
	 * 用.localenv.php里的值替换配置
	 *
	 * @param array $conf        	
	 */
	protected function replaceLocalenv ($conf, $fileName) {
		if (empty($this->mLocalConfMap)) {
			$localConfPath = '.localenv.php';
			if (file_exists($localConfPath)) {
				$this->mLocalConfMap = $this->loadFile($localConfPath);
			}
		}
		$localenvKey = $fileName;
		if (is_array($conf)) {
			if (array_key_exists($localenvKey, $this->mLocalConfMap)) {
				$conf = array_replace_recursive($conf, $this->mLocalConfMap[$localenvKey]);
			}
		}
		
		return $conf;
	}
}