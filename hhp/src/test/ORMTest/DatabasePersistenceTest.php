<?php

namespace test\ORMTest;

use test\AbstractTest;
use hhp\App;
use orm\Condition;

class DatabasePersistenceTest extends AbstractTest {

	public function test () {
		$this->setDatabaseClient();
		$this->save();
		$this->delete();
	}

	public function setDatabaseClient () {
		$this->insert(); // 只要能添加，就说明设置成功了。
	}

	public function save () {
		$this->insert();
		$this->update();
	}

	protected function insert () {
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
		
		$db = App::Instance()->getService('db');
		$dbret = $db->select('SELECT * FROM test_user ORDER BY name');
		if (2 != count($dbret)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		if ('user1' != $dbret[0]['name'] || 'user2' != $dbret[1]['name']) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$dbret = $db->selectRow('SELECT COUNT(1) AS cnt FROM test_group');
		if ($dbret['cnt'] != 1) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$dbret = $db->select('SELECT * FROM test_group2user ORDER BY user_id');
		if (2 != count($dbret)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		// 因为根据依赖关系，保存的顺序应该是先保存u2，所以u2的id会小
		if ($u2->id != $dbret[0]['user_id'] || $g->id != $dbret[0]['group_id'] || $u1->id != $dbret[1]['user_id'] ||
				 $g->id != $dbret[1]['group_id']) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	protected function update () {
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
		
		$u1->name = 'user11';
		$u2->name = 'user22';
		$g->name = 'groupgroup';
		$p->save($u1);
		$p->save($u2);
		$p->save($g);
		
		$db = App::Instance()->getService('db');
		$dbret = $db->select('SELECT * FROM test_user ORDER BY name');
		if ('user11' != $dbret[0]['name'] || 'user22' != $dbret[1]['name']) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$dbret = $db->selectRow('SELECT * FROM test_group');
		if ($dbret['name'] != 'groupgroup') {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function delete () {
		$p = App::Instance()->getService('orm');
		$db = App::Instance()->getService('db');
		
		// 测试多个主键
		// 清空表
		$gu = new TestGroup2User();
		$p->delete(get_class($gu));
		
		$gu->userId = time();
		$gu->groupId = 2;
		$gu->val = microtime();
		$p->save($gu);
		
		$p->delete(get_class($gu));
		
		$dbret = $db->selectOne(
				'SELECT COUNT(1) FROM test_group2user WHERE user_id=' . $gu->userId . ' AND group_id=' . $gu->groupId);
		if (0 != $dbret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$gu->userId = 2;
		$p->save($gu);
		$gu1 = new TestGroup2User();
		$gu1->userId = 4;
		$gu1->groupId = 3;
		$p->save($gu1);
		$cond = new Condition('userId=' . $gu->userId);
		$cond->add('groupId', '=', $gu->groupId);
		$p->delete(get_class($gu1), $cond);
		
		$dbret = $db->selectRow(
				'SELECT * FROM test_group2user WHERE user_id=' . $gu1->userId . ' AND group_id=' . $gu1->groupId);
		if ($gu1->userId != $dbret['user_id'] || $gu1->groupId != $dbret['group_id']) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		$dbret = $db->selectOne('SELECT COUNT(1) AS cnt FROM test_group2user');
		if (1 != $dbret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}
}
?>