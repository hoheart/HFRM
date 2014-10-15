<?php

namespace hhp\view;

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