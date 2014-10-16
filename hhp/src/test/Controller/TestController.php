<?php

namespace test\controller;

/**
 * hhp相关
 */
use hhp\Controller;

/**
 * Test相关
 */
use test\AppTest;
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
	public function indexAction() {
		$arr = array (
				// new Logger(),
				// new File(),
				// new Directory(),
				// new Path(),
				// new Util(),
				// new DatabaseClient(),
				// new DatabaseStatement(),
				new AppTest () 
		);
		
		foreach ( $arr as $t ) {
			try {
				$t->test ();
			} catch ( \Exception $e ) {
				echo 'Test not passed! <br> Error:' . $e->getMessage () . '<br>Class:' . get_class ( $t ) . '<br>Method:' . $t->getErrorMethod () . '<br>Line:' . $t->getErrorLineNumber ();
				echo '<br>';
				echo '<pre>';
				print_r ( $e );
				echo '</pre>';
				break;
			}
		}
		
		echo 'success.';
	}
}
?>