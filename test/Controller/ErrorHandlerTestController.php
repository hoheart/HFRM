<?php

namespace test\controller;

use hhp\Controller;

/**
 * App::trigger方法的测试controller。
 *
 * @author Hoheart
 *        
 */
class ErrorHandlerTestController extends Controller {

	static public function getConfig ($actionName) {
		if ('handle' == $actionName) {
			return array(
				'debug' => false
			);
		} else {
			return array(
				'debug' => true
			);
		}
	}

	public function register2SystemAction () {
		throw new \Exception('aa', 390);
	}

	public function handleShutdownAction () {
		include 'ddd.php';
		echo 11;
	}

	public function handleExceptionAction () {
		throw new \Exception('bb', 391);
	}

	public function handleAction () {
		$this->aab();
	}
}
?>