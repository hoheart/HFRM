<?php

namespace test\Hfc\Database;

use Hfc\Database\DatabaseClient as HDatabaseClient;
use Hfc\Database\DatabaseClientFactory;
use Hfc\Database\DatabaseConnectException;
use Hfc\Database\DatabaseQueryException;
use Hhp\App;
use Hfc\Database\DatabaseResult;
use Hfc\Database\DatabaseTransaction;
use Hfc\Database\DatabaseStatement;
use Hfc\Exception\NotImplementedException;

/**
 * 由于每种数据库管理系统都需要单独的php扩展程序，所以，这儿直接取app配置的数据库测试，
 * 目的是测试需要用到的DBMS。
 * 因为设计的目的是让用户只调用DatabaseClient、DatabaseResult和DatabaseTransaction，所以不测试其他的类。
 *
 * 测试的库名为test123,表为CREATE TABLE test123( id INT autoincreasement , name
 * VARCHAR(32) , password VARCHAR(32)
 * , age INT )
 *
 * @author Hoheart
 *        
 */
class DatabaseClient {

	public function test () {
		if (! $this->connect() || ! $this->exec() || ! $this->select() || ! $this->selectRow() ||
				 ! $this->selectOne() || ! $this->query()) {
			return false;
		}
		
		return true;
	}

	public function connect () {
		$app = App::Instance();
		$db = $app->getService('db');
		
		$sql = "SELECT * FROM test123";
		$db->select($sql);
		$db->query($sql);
		
		try { // 先这么测试，DatabaseStatement再测试是否真的能去任意位置的值。
			$db->query($sql, HDatabaseClient::CURSOR_SCROLL);
		} catch (\Exception $e) {
			if (! $e instanceof NotImplementedException) {
				return false;
			}
		}
		
		$conf = array(
			'dbms' => 'mysql',
			'user' => 'root11',
			'password' => '',
			'server' => '127.0.0.1',
			'port' => 3306,
			'name' => 'mysql',
			'charset' => 'utf8'
		);
		
		$db = null;
		try {
			$f = new DatabaseClientFactory();
			$db = $f->create($conf);
			$db->query("SELECT NOW()");
		} catch (\Exception $e) {
			if (! $e instanceof DatabaseConnectException) {
				return false;
			}
		}
		
		return true;
	}

	public function exec () {
		$db = App::Instance()->getService('db');
		$now = time();
		$sql = "UPDATE test123 SET name = 'update$now' WHERE id = 2";
		$affectedCount = $db->exec($sql);
		if (1 != $affectedCount) {
			return false;
		}
		
		try {
			$db->exec('insert into aaa');
		} catch (\Exception $e) {
			if (! $e instanceof DatabaseQueryException) {
				return false;
			}
		}
		
		return true;
	}

	public function select () {
		$db = App::Instance()->getService('db');
		$ret = $db->select("SELECT * FROM test123 ORDER BY id");
		if (HDatabaseClient::MAX_ROW_COUNT != count($ret)) {
			return false;
		}
		if ($ret[0]['id'] != 1) {
			return false;
		}
		
		$ret = $db->select("SELECT * FROM test123 ORDER BY id", 2, 3);
		if (3 != count($ret)) {
			return false;
		}
		
		if ($ret[0]['id'] != 3) {
			return false;
		}
		
		try {
			$db->select('select aaa from test123');
		} catch (\Exception $e) {
			if (! $e instanceof DatabaseQueryException) {
				return false;
			}
		}
		
		return true;
	}

	public function selectRow () {
		$db = App::Instance()->getService('db');
		$ret = $db->selectRow("SELECT * FROM test123 ORDER BY id");
		if ($ret['id'] != 1) {
			return false;
		}
		
		return true;
	}

	public function selectOne () {
		$db = App::Instance()->getService('db');
		$ret = $db->selectOne("SELECT id FROM test123 ORDER BY id");
		if ($ret != 1) {
			return false;
		}
		
		return true;
	}

	public function query () {
		$db = App::Instance()->getService('db');
		
		$sql = 'SELECT * FROM test123';
		$ret = $db->query($sql);
		if (! $ret instanceof DatabaseStatement && ! is_subclass_of($ret, 'Hfc\Database\DatabaseStatement')) {
			return false;
		}
		
		// 测试能不能查别的
		unset($ret);
		$ret = $db->query($sql);
		if (! $ret instanceof DatabaseStatement) {
			return false;
		}
		unset($ret);
		
		try {
			$db->query('select aaa from test123');
		} catch (\Exception $e) {
			if (! $e instanceof DatabaseQueryException) {
				return false;
			}
		}
		
		return true;
	}

	public function beginTransaction () {
		return true; // 放DatabaseTransaction测试。
	}
}
?>