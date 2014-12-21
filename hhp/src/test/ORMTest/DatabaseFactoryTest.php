<?php

namespace test\ORMTest;

use test\AbstractTest;
use hhp\App;
use orm\DatabaseFactory;
use orm\Condition;

class DatabaseFactoryTest extends AbstractTest {

	public function test () {
		$this->setDatabaseClient();
		$this->get();
		$this->where();
	}

	public function setDatabaseClient () {
		$db = App::Instance()->getService('db');
		
		$f = new DatabaseFactory();
		$f->setDatabaseClient($db);
		
		new TestGroup();
		$f->get('test\ORMTest\TestGroup', 1); // 不报错，就说明对了
	}

	public function get () {
		$db = App::Instance()->getService('db');
		$f = new DatabaseFactory();
		$f->setDatabaseClient($db);
		
		$u1 = new TestUser();
		$u1->name = 'user1';
		$p = App::Instance()->getService('orm');
		$p->save($u1);
		
		$u = $f->get(get_class($u1), $u1->id);
		if ($u->id !== $u1->id || $u->name !== $u1->name) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function where () {
		$db = App::Instance()->getService('db');
		$f = new DatabaseFactory();
		$f->setDatabaseClient($db);
		
		$u1 = new TestUser();
		$u1->name = 'user1';
		$u1->age = 6;
		
		$p = App::Instance()->getService('orm');
		$p->delete(get_class($u1));
		
		$p->save($u1);
		
		$u2 = new TestUser();
		$u2->name = 'user2';
		$u2->age = 6;
		$p->save($u2);
		
		
		$ret = $f->where(get_class($u1), new Condition('age=6'));
		if (! is_array($ret) || 2 != count($ret) || 6 != $ret[0]->age || 6 != $ret[1]->age) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}
}
?>