<?php

namespace test\controller;

/**
 * hhp相关
 */
use hhp\Controller;

/**
 * Test相关
 */
use test\ControllerTest;
use test\AppTest\AppTest;
use test\ErrorHandlerTest;
use test\ServiceManagerTest\ServiceManagerTest;
use test\HfcTest\HfcTest;

class TestController extends Controller {

	public function indexAction () {
		$arr = array(
			new HfcTest(),
			new ServiceManagerTest(),
			new ErrorHandlerTest(),
			new ControllerTest(),
			new AppTest()
		);
		
		$success = true;
		foreach ($arr as $t) {
			try {
				$t->test();
			} catch (\Exception $e) {
				echo 'Test not passed! <br>ErrorMsg:' . $e->getMessage();
				echo '<br>';
				echo '<pre>';
				print_r($e);
				echo '</pre>';
				break;
				
				$success = false;
			}
		}
		
		if ($success) {
			echo 'success.';
		}
	}
}
?>