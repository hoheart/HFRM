<?php

namespace test\ORMTest;

use test\AbstractTest;
use hhp\App;

class DataClassTest extends AbstractTest {

	public function test () {
		$this->set();
		$this->get();
	}

	public function set () {
		$g = new TestGroup();
		$g->name = 'group1';
		
		// 测试普通的get
		if ('group1' !== $g->name) {
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