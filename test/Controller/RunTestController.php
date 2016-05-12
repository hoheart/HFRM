<?php

namespace test\controller;

use hhp\Controller;

/**
 * App::run方法的测试controller。
 * 
 * @author Hoheart
 *        
 */
class RunTestController extends Controller {

	static public function getConfig ($actionName) {
		if ('testExecutor' == $actionName) {
			return array(
				'executor' => array(
					'pre_executor' => array(
						'\test\AppTest\Executor',
						'\test\AppTest\TestPreExecutor2'
					),
					'later_executor' => array(
						'\test\AppTest\TestLaterExecutor1',
						'\test\AppTest\TestLaterExecutor2'
					)
				)
			);
		}
	}

	public function testRedirectionAction () {
		echo 'redirection148';
	}

	public function testArgvAction ($argv) {
		echo $argv['name'];
	}

	public function testExecutorAction ($do) {
		if (! $do instanceof \stdClass) {
			return false;
		}
		
		if ('456' != $do->abc) {
			return false;
		}
		
		return $do;
	}
}
?>