<?php

namespace test\HfcTest\DatabaseTest;

use hhp\App;
use hfc\database\DatabaseStatement;
use test\AbstractTest;

class DatabaseStatementTest extends AbstractTest {
	protected $mDbClient = null;

	public function test () {
		$this->destruct();
		$this->closeCursor();
		$this->rowCount();
		$this->fetch();
		$this->lastInsertId();
	}

	public function destruct () {
		$db = App::Instance()->getService('db');
		
		$sql = 'SELECT * FROM test123';
		$ret = $db->query($sql);
		if (! $ret instanceof DatabaseStatement &&
				 ! is_subclass_of($ret, 'Hfc\Database\DatabaseStatement')) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		unset($ret);
		$ret = $db->query($sql);
	}

	public function closeCursor () {
		$db = App::Instance()->getService('db');
		
		$sql = 'SELECT * FROM test123';
		$ret = $db->query($sql);
		if (! $ret instanceof DatabaseStatement &&
				 ! is_subclass_of($ret, 'Hfc\Database\DatabaseStatement')) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$ret->closeCursor();
		
		$ret = $db->query($sql);
		$ret->closeCursor();
		
		try {
			$ret->closeCursor();
		} catch (\Exception $e) {
			$this->throwError('', __METHOD__, __LINE__); // 再次关闭，不会出错。
		}
	}

	public function rowCount () {
		$db = App::Instance()->getService('db');
		
		$sql = 'SELECT * FROM test123';
		$sql = $db->transLimitSelect($sql, 2, 4);
		$stmt = $db->query($sql);
		if (4 != $stmt->rowCount()) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function fetch () {
		$db = App::Instance()->getService('db');
		
		$sql = 'SELECT * FROM test123 ORDER BY id';
		$stmt = $db->query($sql);
		$fetchRet = $stmt->fetch();
		$ret = $db->selectRow($sql);
		if ($fetchRet != $ret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function lastInsertId () {
		$db = App::Instance()->getService('db');
		
		$sql = "INSERT INTO test123( name , password , age) VALUES ( 'name' , 'password' , 23 )";
		$stmt = $db->query($sql);
		
		$id = $stmt->lastInsertId();
		
		$sql = "SELECT MAX(id) FROM test123";
		$max = $db->selectOne($sql);
		
		if ($id != $max) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}
}
?>