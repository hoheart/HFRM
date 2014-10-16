<?php

namespace test\Hfc\Database;

use Hfc\Database\DatabaseClient as HDatabaseClient;
use Hhp\App;

class DatabaseStatement {
	protected $mDbClient = null;

	public function test () {
		if (! $this->destruct() || ! $this->closeCursor() || ! $this->rowCount() || ! $this->fetch() ||
				 ! $this->lastInsertId()) {
			return false;
		}
		
		return true;
	}

	public function destruct () {
		$db = App::Instance()->getService('db');
		
		$sql = 'SELECT * FROM test123';
		$ret = $db->query($sql);
		if (! $ret instanceof DatabaseStatement && ! is_subclass_of($ret, 'Hfc\Database\DatabaseStatement')) {
			return false;
		}
		
		unset($ret);
		$ret = $db->query($sql);
		
		return true;
	}

	public function closeCursor () {
		$db = App::Instance()->getService('db');
		
		$sql = 'SELECT * FROM test123';
		$ret = $db->query($sql);
		if (! $ret instanceof DatabaseStatement && ! is_subclass_of($ret, 'Hfc\Database\DatabaseStatement')) {
			return false;
		}
		
		$ret->closeCursor();
		
		$ret = $db->query($sql);
		$ret->closeCursor();
		
		try {
			$ret->closeCursor();
		} catch (\Exception $e) {
			return false; // 再次关闭，不会出错。
		}
		
		return true;
	}

	public function rowCount () {
		$db = App::Instance()->getService('db');
		
		$sql = 'SELECT * FROM test123';
		$sql = $db->transLimitSelect($sql, 2, 4);
		$stmt = $db->query($sql);
		if (4 != $stmt->rowCount()) {
			return false;
		}
		
		return true;
	}

	public function fetch () {
		$db = App::Instance()->getService('db');
		
		$sql = 'SELECT * FROM test123 ORDER BY id';
		$stmt = $db->query($sql);
		$fetchRet = $stmt->fetch();
		$ret = $db->selectRow($sql);
		if ($fetchRet != $ret) {
			return false;
		}
		
		return true;
	}

	public function lastInsertId () {
		$db = App::Instance()->getService('db');
		
		$sql = "INSERT INTO test123( name , password , age) VALUES ( 'name' , 'password' , 23 )";
		$stmt = $db->query($sql);
		
		$id = $stmt->lastInsertId();
		
		$sql = "SELECT MAX(id) FROM test123";
		$max = $db->selectOne($sql);
		
		if ($id != $max) {
			return false;
		}
		
		return true;
	}
}
?>