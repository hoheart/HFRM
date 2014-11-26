<?php

namespace test\ORMTest;

use test\AbstractTest;
use orm\DatabasePersistence;
use orm\DatabasePersistenceCreator;

class DatabasePersistenceCreatorTest extends AbstractTest {

	public function test () {
		$this->create();
	}

	public function create () {
		$f = new DatabasePersistenceCreator();
		$p = $f->create(null);
		
		if (! $p instanceof DatabasePersistence) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}
}
?>