<?php

namespace test\controller;

use hhp\Controller;
use test\HfcTest\Event\Event;
use test\HfcTest\Event\Event1;

/**
 * App::run方法的测试controller。
 *
 * @author Hoheart
 *        
 */
class TriggerTestController extends Controller {

	static public function getConfig ($actionName) {
		if ('trigger' == $actionName) {
			return array(
				'service' => array(
					'EventManager' => array(
						'class' => 'hfc\event\EventManager',
						'config' => array(
							'hfc\event\IEvent' => array(
								'test\HfcTest\event\EventHandler',
								'test\HfcTest\event\EventHandler1'
							),
							'hfc\event\CommonEvent' => array(
								'testEvent' => array(
									'test\HfcTest\event\EventHandler2',
									'test\HfcTest\event\EventHandler3'
								),
								'testEvent1' => array(
									'test\HfcTest\event\EventHandler4'
								)
							),
							'test\HfcTest\Event\Event' => array(
								'test\HfcTest\event\EventHandler5',
								'test\HfcTest\event\EventHandler6'
							),
							'test\HfcTest\Event\Event1' => array(
								'test\HfcTest\event\EventHandler7',
								'test\HfcTest\event\EventHandler8'
							)
						)
					)
				)
			);
		}
	}

	public function triggerAction () {
		$app = \hhp\App::Instance();
		
		$app->trigger('testEvent', $this, array(
			'a',
			'b'
		));
		$app->trigger('testEvent1', $this, array(
			'c',
			'd'
		));
		
		$e1 = new Event($this);
		$app->trigger($e1);
		
		$e2 = new Event1($this);
		$app->trigger($e2);
	}
}
?>