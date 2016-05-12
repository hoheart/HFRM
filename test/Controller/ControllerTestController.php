<?php

namespace test\controller;

use hhp\Controller;

/**
 * Controller类的测试controller。
 *
 * @author Hoheart
 *        
 */
class ControllerTestController extends Controller {

	public function assignTest () {
		$this->assign('data', 'data');
	}
}
?>