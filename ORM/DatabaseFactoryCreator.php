<?php

namespace Framework\ORM;

use Framework\ORM\DatabaseFactory;

class DatabaseFactoryCreator {

	public function create (array $conf = null) {
		$f = new DatabaseFactory();
		
		return $f;
	}
}
?>