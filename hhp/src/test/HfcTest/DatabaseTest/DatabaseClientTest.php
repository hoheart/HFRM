<?php

namespace test\HfcTest\DatabaseTest;

use hfc\database\DatabaseClient as HDatabaseClient;
use hfc\database\DatabaseClientFactory;
use hfc\database\DatabaseConnectException;
use Hfc\Database\DatabaseQueryException;
use hhp\App;
use hfc\database\DatabaseStatement;
use hfc\exception\NotImplementedException;
use test\AbstractTest;

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
class DatabaseClientTest extends AbstractTest {

	public function test () {
		$this->connect();
		$this->exec();
		$this->select();
		$this->select2Object();
		$this->selectRow();
		$this->selectOne();
		$this->query();
		$this->change2SqlValue();
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
				$this->throwError('', __METHOD__, __LINE__);
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
				$this->throwError('', __METHOD__, __LINE__);
			}
		}
	}

	public function exec () {
		$db = App::Instance()->getService('db');
		$now = microtime();
		$sql = "UPDATE test123 SET name = 'update$now' WHERE id = 2";
		$affectedCount = $db->exec($sql);
		if (1 != $affectedCount) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		try {
			$db->exec('insert into aaa');
		} catch (\Exception $e) {
			if (! $e instanceof DatabaseQueryException) {
				$this->throwError('', __METHOD__, __LINE__);
			}
		}
	}

	public function select () {
		$db = App::Instance()->getService('db');
		$ret = $db->select("SELECT * FROM test123 ORDER BY id");
		if (HDatabaseClient::MAX_ROW_COUNT != count($ret)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		if ($ret[0]['id'] != 1) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$ret = $db->select("SELECT * FROM test123 ORDER BY id", 2, 3);
		if (3 != count($ret)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		if ($ret[0]['id'] != 3) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		try {
			$db->select('select aaa from test123');
		} catch (\Exception $e) {
			if (! $e instanceof DatabaseQueryException) {
				$this->throwError('', __METHOD__, __LINE__);
			}
		}
	}
	
	public function select2Object(){
		//主要是为orm定做的，所以在orm测试
	}

	public function selectRow () {
		$db = App::Instance()->getService('db');
		$ret = $db->selectRow("SELECT * FROM test123 ORDER BY id");
		if ($ret['id'] != 1) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function selectOne () {
		$db = App::Instance()->getService('db');
		$ret = $db->selectOne("SELECT id FROM test123 ORDER BY id");
		if ($ret != 1) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function query () {
		$db = App::Instance()->getService('db');
		
		$sql = 'SELECT * FROM test123';
		$ret = $db->query($sql);
		if (! $ret instanceof DatabaseStatement &&
				 ! is_subclass_of($ret, 'Hfc\Database\DatabaseStatement')) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		// 测试能不能查别的
		unset($ret);
		$ret = $db->query($sql);
		if (! $ret instanceof DatabaseStatement) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		unset($ret);
		
		try {
			$db->query('select aaa from test123');
		} catch (\Exception $e) {
			if (! $e instanceof DatabaseQueryException) {
				$this->throwError('', __METHOD__, __LINE__);
			}
		}
	}

	public function beginTransaction () {
		return true; // 放DatabaseTransaction测试。
	}

	public function change2SqlValue () {
		$str = '\'`like"=!=<>';
		$db = App::Instance()->getService('db');
		$ret = $db->change2SqlValue($str);
		$r = '\'\\\'`like\\"=!=<>\'';
		if ($r !== $ret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$val = '234';
		$ret = $db->change2SqlValue($val, 'int');
		if (234 !== $ret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$val = '23.4';
		$ret = $db->change2SqlValue($val, 'float');
		if (23.4 !== $ret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$val = \DateTime::createFromFormat('Y-m-d', '2000-08-08');
		$ret = $db->change2SqlValue($val, 'date');
		if ("'2000-08-08'" !== $ret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$val = \DateTime::createFromFormat('Y-m-d H:i:s', '2000-08-08 23:23:23');
		$ret = $db->change2SqlValue($val, 'time');
		if ("'2000-08-08 23:23:23'" !== $ret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$val = \DateTime::createFromFormat('Y-m-d H:i:s', '2000-08-08 23:23:23');
		$ret = $db->change2SqlValue($val, 'datetime');
		if ("'2000-08-08 23:23:23'" !== $ret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}
}
?>