<?php

namespace Framework\View;

use Framework\App;

class HTMLGenerator {
	
	/**
	 *
	 * @var string
	 */
	protected $mStaticUrl = null;

	/**
	 *
	 * @return \HHP\View\HTMLGenerator
	 */
	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new self();
		}
		
		return $me;
	}

	public function __construct () {
		$app = App::Instance();
		$this->mStaticUrl = $app->getConfigValue('staticUrl');
	}

	public function css ($relativeUrl) {
		return '<link rel="stylesheet" href="' . $this->mStaticUrl . $relativeUrl . '" type="text/css" />' . "\n";
	}

	public function script ($relativeUrl) {
		return '<script type="text/javascript" src="' . $this->mStaticUrl . $relativeUrl . '"></script>' . "\n";
	}
}