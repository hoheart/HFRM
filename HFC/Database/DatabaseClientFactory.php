<?php

namespace HFC\Database;

use HFC\Database\Mysql\MysqlClient;
use HFC\Exception\NotImplementedException;
use Framework\Config;

class DatabaseClientFactory {

	public function create (array $conf) {
		$conf['debug'] = Config::Instance()->get('app.debug');
		
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