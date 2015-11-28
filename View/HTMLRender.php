<?php

namespace Framework\View;

/**
 * 注意，一个HTMLRender一次只能render一个view，
 *
 * @author PC
 *        
 */
class HTMLRender {
	
	/**
	 * Section为模板中的一段html，该段html可以作为一个变量插入到其他html中
	 *
	 * @var string
	 */
	protected $mSectionMap = array();
	
	/**
	 *
	 * @var array
	 */
	protected $mSectionNameStack = array();
	
	/**
	 * 当前要渲染的试图
	 *
	 * @var View
	 */
	protected $mCurrentView = null;

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new self();
		}
		
		return $me;
	}

	/**
	 * 渲染并输出试图
	 *
	 * @param View $view        	
	 */
	public function render ($view) {
		$this->mCurrentView = $view;
		
		$this->renderTemplate($view->getDataMap(), $view->getTemplatePath());
		
		// 输出
		foreach ($this->mSectionMap as $section) {
			$this->out($section);
		}
		
		$this->mSectionMap = array();
		$this->mSectionNameStack = array();
	}

	protected function renderTemplate ($data, $tmpl) {
		if (! empty($tmpl)) {
			ob_clean();
			ob_start();
			
			// 把页面最前面的内容当成一个没有名字的section
			$this->mSectionNameStack[] = null;
			
			// array_walk_recursive($data, array(
			// $this,
			// 'change2HTMLValue'
			// ));
			
			extract($data);
			
			include $tmpl;
			
			$this->endPrevSection();
		}
	}

	protected function out ($item) {
		echo htmlspecialchars($item, null, 'UTF-8', true);
	}

	/**
	 * 增加一段section
	 * 该函数只能在模版里调用。
	 *
	 * section分为有名section和无名section
	 * section的内容有可能是模版里的一段html，也可能是另一个View
	 *
	 * @param string $name
	 *        	section的名称，如果是无名的section，该参数可以直接传递页面内容。
	 * @param string $content
	 *        	要么是一个View对象，要么是另一个模版
	 */
	public function section ($name = null, $content = null, $data = array()) {
		$this->endPrevSection();
		
		// 开始自己这段section
		if (null == $content) {
			// 统一转换成有名有content的方式处理
			if ($name instanceof View) {
				$content = $name;
				$name = null;
			} // else{}
		} // else{}
		
		if ($content instanceof View) {
			$this->renderTemplate($content->getDataMap(), $content->getTemplatePath());
			
			$content = null;
		}
		
		$nextName = null;
		if (null !== $content && '' !== $content) {
			// 如果有内容，就是另一个模版
			$currentData = $this->mCurrentView->getDataMap();
			$moduleAlias = $this->mCurrentView->getModuleAlias();
			$data = array_merge($currentData, $data);
			$this->renderTemplate($data, View::ParseViewPath($moduleAlias, $content));
		} else {
			// 内容为空，则该section标签后面的代码是内容
			$nextName = $name;
		}
		
		// 本次渲染结束，进行下次准备
		// 如果没有内容，就是html代码了，下次渲染
		$this->mSectionNameStack[] = $nextName;
		// 前面已经清除内容，现在重新缓冲
		ob_start();
	}

	protected function endPrevSection () {
		// 先清除之前产生的内容（这些内容可能是部分内容，因为在$name指定的section完成之后，还可能继续上个section。）
		$cnt = count($this->mSectionNameStack);
		if ($cnt > 0) {
			$prevContent = ob_get_clean();
			$prevName = array_pop($this->mSectionNameStack);
			if ('' === $prevContent && '' === $prevName) {
				return;
			}
			
			if (null != $prevName) {
				$this->mSectionMap[$prevName] = $prevContent;
			} else {
				$this->mSectionMap[] = $prevContent;
			}
		}
	}

	/**
	 * 结束上一个section
	 */
	public function endSection () {
		$this->endPrevSection();
		
		// 开启下一个无名section
		$this->mSectionNameStack[] = null;
		ob_start();
	}
}