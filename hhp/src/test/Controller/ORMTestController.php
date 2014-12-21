<?php

namespace test\controller;

/**
 * hhp相关
 */
use hhp\Controller;

/**
 * Test相关
 */
use test\ORMTest\DescFactoryTest;
// use test\ORMTest\PhpPersistenceTest;
// use test\ORMTest\PhpFactoryTest;
use test\ORMTest\AbstractDataFactoryTest;
use test\ORMTest\DatabasePersistenceTest;
use test\ORMTest\DatabasePersistenceCreatorTest;
use test\ORMTest\AbstractPersistenceTest;
use test\ORMTest\DatabaseFactoryTest;
use test\ORMTest\DatabaseFactoryCreatorTest;
use test\ORMTest\DataClassTest;
use test\ORMTest\ConditionTest;

class ORMTestController extends Controller {

	public function indexAction () {
		$arr = array(new ConditionTest(),		// new DataClassTest(),
		new DatabaseFactoryTest(),new DatabaseFactoryCreatorTest(),new DatabasePersistenceTest(),
			new DatabasePersistenceCreatorTest(),new AbstractDataFactoryTest(),
			// new PhpFactoryTest(),
			// new PhpPersistenceTest(),
			new AbstractPersistenceTest(),new DescFactoryTest());
		
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