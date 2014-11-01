<?php

namespace hhp\view;

class JsonRender {

	/**
	 * 渲染试图
	 *
	 * @param View $view        	
	 */
	public function render ($view, $e = null) {
		$data = $this->renderTemplate($view->getDataMap(), $view->getTemplatePath());
		$errcode = null == $e ? 0 : $e->getCode();
		$errstr = null == $e ? '' : $e->getMessage();
		
		$this->renderLayout($data, $view->getLayoutPath(), $errcode, $errstr);
	}

	public function renderLayout ($data, $layoutPath, $errcode, $errstr) {
		$jsonObj = include ($layoutPath);
		echo json_encode($jsonObj);
	}

	protected function renderTemplate ($data, $templatePath) {
		extract($data);
		return include $templatePath;
	}
}

?>