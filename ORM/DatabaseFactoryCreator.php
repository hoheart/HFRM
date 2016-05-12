<?php

namespace orm;

use orm\DatabaseFactory;
use hhp\App;

class DatabaseFactoryCreator {

	public function create (array $conf = null) {
		$db = App::Instance()->getService('db');
		
		$f = new DatabaseFactory();
		$f->setDatabaseClient($db);
		
		return $f;
	}
}
?>