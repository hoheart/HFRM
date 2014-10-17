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
				
				echo 'success.';
			} catch ( \Exception $e ) {
				echo 'Test not passed! <br>' . $e->getMessage ();
				echo '<br>';
				echo '<pre>';
				print_r ( $e );
				echo '</pre>';
				break;
			}
		}
	}
}
?>