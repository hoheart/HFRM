<?php

namespace Framework;

use Framework\View\View;
use HFC\Exception\ParameterErrorException;

abstract class Controller {
	
	/**
	 * 模块别名
	 *
	 * @var string
	 */
	protected $mModuleAlias = null;
	
	/**
	 * 试图类
	 *
	 * @var View
	 */
	protected $mView = null;

	public function __construct ($alias) {
		$this->mModuleAlias = $alias;
	}

	public function setView ($name, $viewType = View::VIEW_TYPE_HTML) {
		$this->mView = new View($this->mModuleAlias, $name, $viewType);
	}

	/**
	 * 该方法直接输出viewName指定的模版（默认为common::Common.jsonDefault）,数据为$data。
	 *
	 * @param array $data        	
	 * @param string $viewName        	
	 */
	public function setJsonView ($data, $viewName = 'common::Common.jsonDefault') {
		$this->mView = new View($this->mModuleAlias, $viewName, View::VIEW_TYPE_JSON);
		
		$this->assign('data', $data);
	}

	public function getView () {
		return $this->mView;
	}

	protected function assign ($key, $val) {
		if (null == $this->mView) {
			throw new ParameterErrorException('call setView first.');
		}
		
		$this->mView->assign($key, $val);
	}

	/**
	 * 取得对应action独有的配置，主要是运行该action，需要增加活修改全局配置的项目。
	 *
	 * @param string $actionName
	 *        	不带Action字样的方法名。
	 */
	static public function getConfig ($actionName) {
	}
}