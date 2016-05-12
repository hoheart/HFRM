<?php

namespace hhp\view;

use hhp\App;

/**
 * 视图类。
 * 其就是一个容器，包含了数据、模版、布局。
 *
 * @author Hoheart<youkj@yonyou.com>
 *        
 */
class View {
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
	 * 布局文件路径
	 *
	 * @var string
	 */
	protected $mLayoutPath = '';

	public function __construct ($name, $layoutPath = null) {
		if (null === $layoutPath) {
			$layoutPath = App::Instance()->getConfigValue('default_layout');
			$this->mLayoutPath = $layoutPath;
		}
		
		list ($moduleName, $ctrlName, $actionName) = explode('/', $name);
		$confModuleArr = App::Instance()->getConfigValue('module');
		$moduleDir = $confModuleArr[$moduleName]['dir'];
		
		$path = $moduleDir . 'view' . DIRECTORY_SEPARATOR . $ctrlName . DIRECTORY_SEPARATOR . $actionName . '.php';
		$this->mTemplatePath = $path;
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

	/**
	 * 设置布局文件的绝对路径
	 *
	 * @param string $path        	
	 */
	public function setLayoutPath ($path) {
		$this->mLayoutPath = $path;
	}

	public function getLayoutPath () {
		return $this->mLayoutPath;
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
}

?>