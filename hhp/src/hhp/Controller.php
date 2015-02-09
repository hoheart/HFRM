<?php

namespace hhp;

use hhp\view\View;

abstract class Controller {
	
	/**
	 * 试图类
	 *
	 * @var View
	 */
	protected $mView = null;
	
	/**
	 * 请求
	 *
	 * @var IRequest
	 */
	protected $mRequest = null;

	public function __construct (IRequest $request = null) {
		$this->mRequest = $request;
	}

	public function getView () {
		return $this->mView;
	}

	protected function assign ($key, $val) {
		if (null == $this->mView) {
			$this->mView = new View();
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