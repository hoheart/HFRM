<?php

namespace test\ORMTest;

use test\AbstractTest;
use hhp\App;
use orm\DatabaseFactory;
use orm\DatabaseFactoryCreator;

class DatabaseFactoryTest extends AbstractTest {

	public function test () {
	}

	public function setDatabaseClient () {
		$db = App::Instance()->getService('db');
		
		$f = new DatabaseFactory();
		$f->setDatabaseClient($db);
		
		$f->getDataMapList('test\ORMTest\TestGroup'); // 不报错，就说明对了
	}

	public function getDataMapList () {
		$c = new DatabaseFactoryCreator();
		$f = $c->create();
		
		$u = new TestUser();
		$u->name = 'user1';
		$u->age = '34';
		$u->amount = 1500.23;
		$u->birthday = \DateTime::createFromFormat('2000-08-08');
		$u->registerTime = \DateTime::createFromFormat('2014-10-20 22:22:22');
		$u->female = true;
		
		$clsName = get_class($u);
		$p = App::Instance()->getService('databasePersistence');
		$p->delete($clsName);
		$p->save($u);
		
		$arr = array();
		$ret = $f->getDataMapList($clsName);
		if ($arr != $ret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}
}
?>