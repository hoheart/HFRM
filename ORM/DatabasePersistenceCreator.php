<?php

namespace orm;

use orm\DatabasePersistence;
use hhp\App;

class DatabasePersistenceCreator {

	public function create (array $conf = null) {
		$db = App::Instance()->getService('db');
		
		$p = new DatabasePersistence();
		$p->setDatabaseClient($db);
		
		return $p;
	}
}
?>