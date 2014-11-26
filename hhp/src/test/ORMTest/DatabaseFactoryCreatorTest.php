<?php

namespace test\ORMTest;

use test\AbstractTest;
use orm\DatabaseFactoryCreator;
use orm\DatabaseFactory;

class DatabaseFactoryCreatorTest extends AbstractTest {

	public function test () {
		$this->create();
	}

	public function create () {
		$f = new DatabaseFactoryCreator();
		$p = $f->create(null);
		
		if (! $p instanceof DatabaseFactory) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}
}
?>