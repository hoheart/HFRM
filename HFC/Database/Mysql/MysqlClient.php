<?php

namespace Framework\HFC\Database\Mysql;

use Framework\HFC\Database\PDOClient;

class MysqlClient extends PDOClient {

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
					if ($val instanceof \DateTime) {
						$v = $val->format('Y-m-d');
					}
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