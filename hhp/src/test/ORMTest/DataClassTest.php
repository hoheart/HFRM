<?php

namespace test\ORMTest;

use test\AbstractTest;
use hfc\exception\ParameterErrorException;
use hhp\App;

class DataClassTest extends AbstractTest {

	public function test () {
		$this->set();
		$this->get();
	}

	public function set () {
		$u = new TestUser();
		
		$pass = false;
		try {
			$u->aaa = 1;
		} catch (ParameterErrorException $e) {
			$pass = true;
		}
		if (! $pass) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$u->name = 'user1';
		$u->age = '34';
		$u->amount = '1500.23';
		$u->birthday = '2000-08-08';
		$u->registerTime = '2014-10-20 22:22:22';
		$u->female = 1;
		if ('user1' !== $u->name) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		if (34 !== $u->age) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		if (1500.23 !== $u->amount) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		if ('2000-08-08 00:00:00' !== $u->birthday->format('Y-m-d H:i:s')) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		if ('2014-10-20 22:22:22' !== $u->registerTime->format('Y-m-d H:i:s')) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		if (true !== $u->female) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function get () {
		$g = new TestGroup();
		$g->name = 'group1';
		
		// 测试普通的get
		if ('group1' !== $g->name) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		// 测试类数组的获取
		$u1 = new TestUser();
		$u1->name = 'user1';
		$u2 = new TestUser();
		$u2->name = 'user2';
		
		$g->userArr = array(
			$u1,
			$u2
		);
		
		$gu = new TestGroup2User();
		$gu->userId = $u1->id;
		$gu->groupId = $g->id;
		
		$p = App::Instance()->getService('databasePersistence');
		
		$p->delete(get_class($g));
		$p->delete(get_class($u1));
		$p->delete(get_class($gu));
		
		$p->save($g, true);
		
		$groupId = $g->id;
		
		$g1 = new TestGroup();
		$g1->id = $groupId;
		
		$userArr = $g1->userArr;
		if ($userArr !== $g->userArr) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		// 测试单个类的获取
		$u1 = new TestUser1();
		$u1->groupId = $g->id;
		if ($u1->group == $g) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}
}
?>