<?php

namespace Framework\View;

use Framework\App;

class JsonRender {
	
	/**
	 * 视图树
	 *
	 * @var array
	 */
	protected $mTree = array();

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new self();
		}
		
		return $me;
	}

	/**
	 * 渲染试图
	 *
	 * @param View $view        	
	 */
	public function render (View $view) {
		$previousContent = ob_get_contents();
		ob_end_clean();
		if ($previousContent != '') {
			// 因为json是严格的一棵树，如果前面已经有输出了，会破坏这棵树的结构，所以在输出也是没有意义的。
			App::Instance()->getOutputStream()->write($previousContent);
			return;
		}
		
		$outputStream = App::Instance()->getOutputStream();
		$outputStream->status(200);
		$outputStream->header('Content-Type', 'application/json; charset=utf-8');
		
		extract($view->getDataMap());
		
		$ret = include $view->getTemplatePath();
		if (is_array($ret)) {
			$this->mTree = $ret;
		}
		
		App::Instance()->getOutputStream()->write(json_encode($this->mTree));
		App::Instance()->getOutputStream()->flush();
	}

	public function node ($path, $data, $template = null) {
		if (null != $template) {
			// template是模版，如果没有指定模版，那path指定的肯定是模版路径，而不会是其他
			if (is_array($data)) {
				extract($data);
			}
			$this->mTree = include View::ParseViewPath('', $template);
		} else {
			$arr = explode('.', $path);
			if (! empty($arr)) {
				$local = &$this->mTree;
				foreach ($arr as $val) {
					$local = &$local[$val];
				}
				
				$local = $data;
			} else {
				$local = $data;
			}
		}
	}
}
