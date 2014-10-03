<?php

namespace test\Controller;

/**
 * Hhp相关
 */
use Hhp\Controller;

/**
 * Test相关
 */
use test\App;
use Hhp\View\View;
use test\Hfc\Util\Util;
use test\Hfc\Database\DatabaseClient;
use test\Hfc\Database\DatabaseStatement;
use test\Hfc\Event\Event;
use test\Hfc\Event\Event1;
use test\Hfc\Util\Logger;
use test\Hfc\IO\Path;
use test\Hfc\IO\Directory;
use test\Hfc\IO\File;

class TestController extends Controller {

	public function getConfig ($actionName) {
		if ('executerTester' == $actionName) {
			return array(
				'route' => array(
					'pre_executer' => array(
						'test\Executer',
						'test\TestPreExecuter2'
					),
					'later_executer' => array(
						'test\TestLaterExecuter1',
						'test\TestLaterExecuter2'
					)
				)
			);
		} else if ('eventTester' == $actionName) {
			return array(
				'service' => array(
					'EventManager' => array(
						'class' => 'Hfc\Event\EventManager',
						'config' => array(
							'Hfc\Event\IEvent' => array(
								'test\Hfc\Event\EventHandler',
								'test\Hfc\Event\EventHandler1'
							),
							'Hfc\Event\CommonEvent' => array(
								'testEvent' => array(
									'test\Hfc\Event\EventHandler2',
									'test\Hfc\Event\EventHandler3'
								),
								'testEvent1' => array(
									'test\Hfc\Event\EventHandler4'
								)
							),
							'test\Hfc\Event\Event' => array(
								'test\Hfc\Event\EventHandler5',
								'test\Hfc\Event\EventHandler6'
							),
							'test\Hfc\Event\Event1' => array(
								'test\Hfc\Event\EventHandler7',
								'test\Hfc\Event\EventHandler8'
							)
						)
					)
				)
			);
		}
	}

	public function index () {
		$arr = array(
			new Logger(),
			new File(),
			new Directory(),
			new Path(),
			new Util(),
			new DatabaseClient(),
			new DatabaseStatement(),
			new App()
		);
		
		foreach ($arr as $t) {
			try {
				if (! $t->test()) {
					throw new \Exception('');
				}
			} catch (\Exception $e) {
				echo 'Test not passed. Error Class: ' . get_class($t) . '<br>Message:' .
						 $e->getMessage();
				echo '<pre>';
				print_r($e);
				echo '</pre>';
				break;
			}
		}
		
		echo 'success.';
	}

	public function executerTester ($do) {
		echo 'aa';
		
		if (! $do instanceof \stdClass) {
			return false;
		}
		
		if ('edf' != $do->edf) {
			return false;
		}
		
		$v = new View();
		
		return $v;
	}

	public function eventTester () {
		$app = \Hhp\App::Instance();
		
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