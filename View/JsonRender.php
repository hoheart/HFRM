<?php

namespace Framework\View;

use Framework\Config;

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
		header('Content-Type: application/json');
		
		if (Config::Instance()->get('app.debugOutput')) {
			ob_flush();
			flush();
		} else {
			ob_clean();
		}
		
		// 因为json是严格的一棵树，如果前面已经有输出了，会破坏这棵树的结构，所以在输出也是没有意义的。
		$previousContent = ob_get_contents();
		if ($previousContent != '') {
			ob_clean();
			echo $previousContent;
			return;
		}
		
		extract($view->getDataMap());
		
		$ret = include $view->getTemplatePath();
		if (is_array($ret)) {
			$this->mTree = $ret;
		}
		
		echo json_encode($this->mTree);
		
		//如果不及时向客户端输出，app会吧缓存清除
		ob_flush();
		flush();
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
