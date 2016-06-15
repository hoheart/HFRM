<?php

namespace Framework\HFC\Database;

use Framework\HFC\Database\Mysql\MysqlClient;
use Framework\HFC\Exception\NotImplementedException;
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