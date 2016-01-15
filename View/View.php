<?php

namespace Framework\View;

use Framework\App;
use HFC\Exception\ParameterErrorException;
use Framework\Module\ModuleManager;

/**
 * 视图类。
 * 其就是一个容器，包含了数据、模版、布局。
 *
 * @author Hoheart
 *        
 */
class View {
	
	/**
	 *
	 * @var integer
	 */
	const VIEW_TYPE_UNKNOWN = 0;
	const VIEW_TYPE_HTML = 1;
	const VIEW_TYPE_JSON = 2;
	
	/**
	 * 数据map，存放controller assign的键值对数据。
	 *
	 * @var array
	 */
	protected $mDataMap = array();
	
	/**
	 * 视图文件路径
	 *
	 * @var string
	 */
	protected $mTemplatePath = '';
	
	/**
	 */
	protected $mCurrentModuleAlias = '';
	
	/**
	 *
	 * @var integer
	 */
	protected $mViewType = self::VIEW_TYPE_UNKNOWN;

	public function __construct ($myAlias, $name = '', $type = self::VIEW_TYPE_HTML) {
		if (empty($name)) {
			throw new ParameterErrorException('did not assign view name.');
		}
		
		if (self::VIEW_TYPE_JSON == $type) {
			$this->mViewType = $type;
		} else {
			$this->mViewType = self::VIEW_TYPE_HTML;
		}
		
		$this->mCurrentModuleAlias = $myAlias;
		$this->mTemplatePath = self::ParseViewPath($myAlias, $name);
	}

	static public function ParseViewPath ($myAlias, $name) {
		$arr = explode('::', $name);
		$path = '';
		if (1 == count($arr)) {
			$moduleAlias = $myAlias;
			$path = $arr[0];
		} else {
			$moduleAlias = $arr[0];
			$path = $arr[1];
		}
		
		$path = str_replace('.', DIRECTORY_SEPARATOR, $path);
		$moduleDir = ModuleManager::Instance()->getModulePath($moduleAlias);
		
		// $ext = self::VIEW_TYPE_JSON == $this->mViewType ? '.json.php' :
		// '.php';
		
		$path = App::$ROOT_DIR . $moduleDir . 'View' . DIRECTORY_SEPARATOR . $path . '.php';
		
		return $path;
	}

	public function getModuleAlias () {
		return $this->mCurrentModuleAlias;
	}

	/**
	 * 数据赋值
	 *
	 * @param string $key        	
	 * @param object $val        	
	 */
	public function assign ($key, $val) {
		$this->mDataMap[$key] = $val;
	}

	public function setTemplatePath ($path) {
		$this->mTemplatePath = $path;
	}

	public function getTemplatePath () {
		return $this->mTemplatePath;
	}

	public function getDataMap () {
		return $this->mDataMap;
	}

	public function setType ($type) {
		if (self::VIEW_TYPE_HTML == $type || self::VIEW_TYPE_JSON == $type) {
			$this->mViewType = $type;
		} else {
			throw new ParameterErrorException();
		}
	}

	public function getType () {
		return $this->mViewType;
	}
}