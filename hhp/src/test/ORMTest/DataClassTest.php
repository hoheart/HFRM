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
		
		$p = App::Instance()->getService('orm');
		
		$gu = new TestGroup2User();
		
		$p->delete(get_class($g));
		$p->delete(get_class($u1));
		$p->delete(get_class($gu));
		
		$p->save($g, true);
		
		$gu->userId = $u1->id;
		$gu->groupId = $g->id;
		
		$gu1 = new TestGroup2User();
		$gu1->groupId = $g->id;
		$gu1->userId = $u2->id;
		
		$p->save($gu);
		$p->save($gu1);
		
		$groupId = $g->id;
		
		$g1 = new TestGroup();
		$g1->id = $groupId;
		
		$userArr = $g1->userArr;
		if (count($userArr) != count($g->userArr) || $userArr[0]->id != $g->userArr[0]->id ||
				 $userArr[1]->id != $g->userArr[1]->id) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}
}
?>