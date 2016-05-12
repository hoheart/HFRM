<?php

namespace test\ServiceManagerTest;

use hhp\App;

/**
 * 对hhp框架的App类进行测试
 *
 * @author Hoheart
 *        
 */
class ServiceManagerTest extends \test\AbstractTest {

	public function test () {
		$this->getService();
	}

	public function getService () {
		$app = App::Instance();
		
		$s = $app->getService('serviceOnly');
		$ret = $s->getParam();
		if (123 != $ret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$s = $app->getService('serviceFactory');
		$ret = $s->getParam();
		if (234 != $ret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}
}