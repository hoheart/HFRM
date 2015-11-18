<?php

namespace Framework;

use Framework\View\View;
use HFC\Exception\ParameterErrorException;

abstract class Controller {
	
	/**
	 * 请求终端端的来源
	 *
	 * @var integer
	 */
	const TERMINAL_TYPE_UNKNOW = 0;
	const TERMINAL_TYPE_WEB = 1;
	const TERMINAL_TYPE_ANDROID_PHONE = 2;
	const TERMINAL_TYPE_IPHONE = 3;
	const TERMINAL_TYPE_WAP = 4;
	const TERMINAL_TYPE_ANDROID_PAD = 5;
	const TERMINAL_TYPE_IPAD = 6;
	
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
		
		Session::Instance()->set('FRM_terminalType', self::TERMINAL_TYPE_WEB);
	}

	public function setView ($name, $viewType = View::VIEW_TYPE_HTML) {
		$this->mView = new View($this->mModuleAlias, $name, $viewType);
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

?>