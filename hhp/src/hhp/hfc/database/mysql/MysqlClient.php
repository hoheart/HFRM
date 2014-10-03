<?php

namespace Hfc\Database\Mysql;

use Hfc\Database\PDOClient;
use Hfc\Exception\NotImplementedException;

class MysqlClient extends PDOClient {

	public function query ($sql, $cursorType = self::CURSOR_FWDONLY) {
		if (self::CURSOR_FWDONLY != $cursorType) {
			throw new NotImplementedException('mysql database can not support scroll cursor.');
		}
		
		return parent::query($sql, $cursorType);
	}

	protected function getDSN () {
		$host = $this->mConf['server'];
		$port = $this->mConf['port'];
		$dbname = $this->mConf['name'];
		$charset = $this->mConf['charset'];
		$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
		
		return $dsn;
	}
}
?>