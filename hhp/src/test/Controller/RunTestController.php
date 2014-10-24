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
						'\test\Executor',
						'\test\TestPreExecutor2'
					),
					'later_executor' => array(
						'\test\TestLaterExecutor1',
						'\test\TestLaterExecutor2'
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