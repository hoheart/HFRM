<?php

namespace test\controller;

/**
 * Hhp相关
 */
use hhp\Controller;

/**
 * Test相关
 */
use test\App;
use hhp\View\View;
use test\hfc\util\Util;
use test\hfc\Database\DatabaseClient;
use test\hfc\Database\DatabaseStatement;
use test\hfc\Event\Event;
use test\hfc\Event\Event1;
use test\hfc\Util\Logger;
use test\hfc\IO\Path;
use test\hfc\IO\Directory;
use test\hfc\IO\File;

class TestController extends Controller {

	public function indexAction () {
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