<?php

namespace test\HfcTest;

use test\AbstractTest;
use test\HfcTest\DatabaseTest\DatabaseStatementTest;
use test\HfcTest\DatabaseTest\DatabaseClientTest;
use test\HfcTest\UtilTest\UtilTest;
use test\HfcTest\UtilTest\LoggerTest;
use test\HfcTest\IOTest\PathTest;
use test\HfcTest\IOTest\DirectoryTest;
use test\HfcTest\IOTest\FileTest;

class HfcTest extends AbstractTest {

	public function test () {
		$arr = array(
			new FileTest(),
			new DirectoryTest(),
			new PathTest(),
			new LoggerTest(),
			new UtilTest(),
			new DatabaseClientTest(),
			new DatabaseStatementTest()
		);
		
		foreach ($arr as $t) {
			$t->test();
		}
	}
}

?>