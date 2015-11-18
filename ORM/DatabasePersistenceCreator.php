<?php

namespace Framework\ORM;

use Framework\ORM\DatabasePersistence;

class DatabasePersistenceCreator {

	public function create (array $conf = null) {
		$p = new DatabasePersistence();
		
		return $p;
	}
}
?>