<?php

namespace test;

use test\controller\ControllerTestController;
use hhp\view\View;

/**
 * 对hhp框架的App类进行测试
 *
 * @author Hoheart
 *        
 */
class ControllerTest extends \test\AbstractTest {

	public function test () {
		$this->getView();
	}

	public function getView () {
		$ctrl = new ControllerTestController();
		$ctrl->assignTest();
		
		if (! $ctrl->getView() instanceof View) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}
}