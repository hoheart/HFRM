<?php

namespace HFC\Database\Mysql;

use HFC\Database\PDOClient;
use HFC\Exception\NotImplementedException;

class MysqlClient extends PDOClient {
	protected $mInited = false;

	public function query ($sql, $cursorType = self::CURSOR_FWDONLY) {
		if (self::CURSOR_FWDONLY != $cursorType) {
			throw new NotImplementedException('mysql database can not support scroll cursor.');
		}
		
		return parent::query($sql, $cursorType);
	}

	protected function getClient () {
		$client = parent::getClient();
		if (! $this->mInited) {
			$this->mInited = true;
			
			$dt = new \DateTime();
			$tz = $dt->getTimezone();
			
			$intTz = (int) ($tz->getOffset($dt) / 3600);
			if ($intTz > 0) {
				$intTz = '+' . $intTz;
			}
			$client->exec("SET time_zone = '$intTz:00';COMMIT;");
		}
		
		return $client;
	}

	protected function getDSN () {
		$host = $this->mConf['server'];
		$port = $this->mConf['port'];
		$dbname = $this->mConf['name'];
		$charset = $this->mConf['charset'];
		$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
		
		return $dsn;
	}

	public function change2SqlValue ($val, $type = 'string') {
		if (null === $val) {
			return 'null';
		}
		
		$type = strtolower($type);
		if ("\\" == $type[0]) {
			$type = substr($type, 1);
		}
		if (is_array($val)) {
			$ret = array();
			foreach ($val as $oneVal) {
				$ret[] = $this->change2SqlValue($oneVal, $type);
			}
			
			return $ret;
		}
		
		$v = null;
		if ('string' == substr($type, 0, 6)) {
			$v = $this->getClient()->quote($val);
		} else if ('int' == substr($type, 0, 3)) {
			$v = (int) $val;
		} else if ('float' == substr($type, 0, 5)) {
			$v = (float) $val;
		} else {
			switch ($type) {
				case 'date':
					$v = $val->format('Y-m-d');
					break;
				case 'time':
				case 'datetime':
					$v = $val->format('Y-m-d H:i:s');
					break;
				case 'boolean':
					$v = $val ? 1 : 0;
					break;
			}
			
			$v = "'$v'";
		}
		
		return $v;
	}
}