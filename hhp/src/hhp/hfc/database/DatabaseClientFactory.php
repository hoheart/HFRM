<?php

namespace Hfc\Database;

use Hfc\Database\Mysql\MysqlClient;
use Hfc\Exception\NotImplementedException;

class DatabaseClientFactory {

	public function create (array $conf) {
		$client = null;
		switch ($conf['dbms']) {
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