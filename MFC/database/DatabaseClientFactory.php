<?php

namespace hfc\Database;

use hfc\database\mysql\MysqlClient;
use hfc\exception\NotImplementedException;

class DatabaseClientFactory {

	public function create (array $conf) {
		$client = null;
		$dbms = $conf['dbms'];
		switch ($dbms) {
			case 'mysql':
				$client = new MysqlClient($conf);
				break;
			default:
				throw new NotImplementedException('the DBMS: ' . $dbms . ' not support yet.');
				break;
		}
		
		return $client;
	}
}
?>