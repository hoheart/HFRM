<?php

namespace Framework\View;

use Framework\App;
use Framework\Request\HttpRequest;
use Framework\Config;

/**
 * 试图渲染器。该类是一个工厂类，根据用户的请求等，判断应该调用什么试图渲染器进行渲染。
 *
 * @author Hoheart
 *        
 */
class ViewRender {
	
	/**
	 *
	 * @var array
	 */
	protected $mRenderList = array();

	static public function Instance () {
		$me = null;
		if (null == $me) {
			$me = new self();
		}
		
		return $me;
	}

	public function render ($v = null) {
		// 设置时间戳
		$tmzone = Config::Instance()->get('app.localTimezone');
		// date_default_timezone_set($tmzone);
		
		// $v 一般等于null，Controller一般不会返回任何数据
		$ctrl = App::Instance()->getCurrentController();
		$v = $ctrl->getView();
		if (null == $v) {
			if (HttpRequest::isAjaxRequest()) {
				$v = new View('', 'common::Common.frame', View::VIEW_TYPE_JSON);
				$v->assign('errcode', 0);
			} else {
				return;
			}
		}
		
		$viewType = View::VIEW_TYPE_UNKNOWN;
		$ret = null;
		
		if (View::VIEW_TYPE_JSON == $v->getType()) {
			$viewType = $v->getType();
		} else {
			$viewType = View::VIEW_TYPE_HTML;
		}
		
		$render = null;
		switch ($viewType) {
			case View::VIEW_TYPE_HTML:
			case View::VIEW_TYPE_UNKNOWN:
				$render = $this->mRenderList[$viewType];
				if (null == $render) {
					$render = HTMLRender::Instance();
					$this->mRenderList[$viewType] = $render;
				}
				
				break;
			case View::VIEW_TYPE_JSON:
				$render = $this->mRenderList[$viewType];
				if (null == $render) {
					$render = JsonRender::Instance();
					$this->mRenderList[$viewType] = $render;
				}
				
				break;
		}
		
		$ret = $render->render($v);
		
		return $ret;
	}
}