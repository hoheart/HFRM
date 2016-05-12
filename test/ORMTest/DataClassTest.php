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
		
		$g = new TestGroup();
		$g->name = 'group1';
		$g->userArr = array($u1,$u2);
		$g->oneUser = $u2;
		
		$u1->group = $g;
		
		$p = App::Instance()->getService('orm');
		
		$p->delete(get_class($g));
		$p->delete(get_class($u1));
		$o = new TestGroup2User();
		$p->delete(get_class($o));
		
		$p->save($u1);
		$p->save($u2);
		$p->save($g);
		
		$gg = $p->get(get_class($g), $g->id);
		$userArr = $gg->userArr;
		foreach ($userArr as $user) {
			if (! in_array($user->name, array('user1','user2'))) {
				$this->throwError('', __METHOD__, __LINE__);
			}
		}
	}
}
?>