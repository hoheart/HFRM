<?php

namespace test\ORMTest;

use test\AbstractTest;
use hhp\App;
use orm\Condition;
use orm\DescFactory;

class DatabasePersistenceTest extends AbstractTest {

	public function test () {
		$this->setDatabaseClient();
		$this->add();
		$this->update();
		$this->change2SqlValue();
		$this->delete();
	}

	public function setDatabaseClient () {
		$this->add(); // 只要能添加，就说明设置成功了。
	}

	public function add () {
		$this->insertOneKey();
		$this->insertMultiKey();
		$this->insertSub();
	}

	public function update () {
		$this->updateOneKey();
		$this->updateMultiKey();
	}

	protected function insertOneKey () {
		$p = App::Instance()->getService('databasePersistence');
		$db = App::Instance()->getService('db');
		
		// 测试一个主键
		$g = new TestGroup();
		$g->name = 'group' . microtime();
		// 清空表
		$p->delete(get_class($g));
		$ret = $p->add($g);
		if (- 1 != $ret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$dbret = $db->selectRow('SELECT * FROM test_group WHERE id=' . $g->id);
		if ($dbret['name'] != $g->name) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	protected function insertMultiKey () {
		$p = App::Instance()->getService('databasePersistence');
		$db = App::Instance()->getService('db');
		
		// 测试多个主键
		// 清空表
		$gu = new TestGroup2User();
		$p->delete(get_class($gu));
		
		$gu->userId = time();
		$gu->groupId = 2;
		$gu->val = microtime();
		$ret = $p->add($gu);
		if (- 1 != $ret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$dbret = $db->selectRow(
				'SELECT * FROM test_group2user WHERE user_id=' . $gu->userId . ' AND group_id=' .
						 $gu->groupId);
		if ($dbret['val'] != $gu->val) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	protected function insertSub () {
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
		$g->oneUser = $u1;
		
		$u1->group = $g;
		
		$gu = new TestGroup2User();
		$gu->userId = $u1->id;
		$gu->groupId = $g->id;
		
		$p = App::Instance()->getService('databasePersistence');
		
		$p->delete(get_class($g));
		$p->delete(get_class($u1));
		$p->delete(get_class($gu));
		
		$p->save($g, true);
		
		$db = App::Instance()->getService('db');
		$dbret = $db->selectRow('SELECT COUNT(1) AS cnt FROM test_user');
		if ($dbret['cnt'] != 2) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$dbret = $db->selectRow('SELECT COUNT(1) AS cnt FROM test_group');
		if ($dbret['cnt'] != 1) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	protected function updateOneKey () {
		$p = App::Instance()->getService('databasePersistence');
		$db = App::Instance()->getService('db');
		
		// 测试一个主键
		$g = new TestGroup();
		$g->name = 'group' . microtime();
		// 清空表
		$p->delete(get_class($g));
		
		$p->save($g);
		$g->name .= 'a';
		$ret = $p->update($g);
		if (1 != $ret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$dbret = $db->selectRow('SELECT * FROM test_group WHERE id=' . $g->id);
		if ($dbret['name'] != $g->name) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	protected function updateMultiKey () {
		$p = App::Instance()->getService('databasePersistence');
		$db = App::Instance()->getService('db');
		
		// 测试多个主键
		// 清空表
		$gu = new TestGroup2User();
		$p->delete(get_class($gu));
		
		$gu->userId = time();
		$gu->groupId = 2;
		$gu->val = microtime();
		$p->add($gu);
		$gu->val .= 'b';
		$ret = $p->update($gu);
		if (1 != $ret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$dbret = $db->selectRow(
				'SELECT * FROM test_group2user WHERE user_id=' . $gu->userId . ' AND group_id=' .
						 $gu->groupId);
		if ($dbret['val'] != $gu->val) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function change2SqlValue () {
		$g = new TestGroup();
		$g->name = 'group1';
		
		$u = new TestUser();
		$u->name = 'user1';
		$u->age = '34';
		$u->amount = 1500.23;
		$u->birthday = \DateTime::createFromFormat('Y-m-d', '2000-08-08');
		$u->registerTime = \DateTime::createFromFormat('Y-m-d H:i:s', '2014-10-20 22:22:22');
		$u->female = true;
		
		$p = App::Instance()->getService('databasePersistence');
		$clsDesc = DescFactory::Instance()->getDesc(get_class($u));
		$ret = $p->change2SqlValue($u, $clsDesc->attribute, false);
		
		$correct = array(
			array(
				'name',
				'age',
				'amount',
				'birthday',
				'register_time',
				'female'
			),
			array(
				'user1',
				34,
				1500.23,
				'2000-08-08',
				'2014-10-20 22:22:22',
				true
			)
		);
		if ($correct != $ret) {
			return false;
		}
		
		$this->testSaveSub($u);
	}

	protected function testSaveSub ($p, $u) {
		$db = App::Instance()->getService('db');
		
		// 测试多个主键
		// 清空表
		$p->delete(get_class($u));
		$p->delete(get_class($u->group));
		
		$p->add($u, true);
		$dbret = $db->selectOne(
				'SELECT COUNT(1) FROM test_group2user WHERE user_id=' . $gu->userId .
						 ' AND group_id=' . $gu->groupId);
		if (0 != $dbret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function delete () {
		$p = App::Instance()->getService('databasePersistence');
		$db = App::Instance()->getService('db');
		
		// 测试多个主键
		// 清空表
		$gu = new TestGroup2User();
		$p->delete(get_class($gu));
		
		$gu->userId = time();
		$gu->groupId = 2;
		$gu->val = microtime();
		$p->add($gu);
		
		$p->delete(get_class($gu));
		
		$dbret = $db->selectOne(
				'SELECT COUNT(1) FROM test_group2user WHERE user_id=' . $gu->userId .
						 ' AND group_id=' . $gu->groupId);
		if (0 != $dbret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$gu->userId = 2;
		$p->add($gu);
		$gu1 = new TestGroup2User();
		$gu1->userId = 4;
		$gu1->groupId = 3;
		$p->add($gu1);
		$cond = new Condition('userId=' . $gu->userId);
		$cond->add('groupId', '=', $gu->groupId);
		$p->delete(get_class($gu1), $cond);
		
		$dbret = $db->selectRow(
				'SELECT * FROM test_group2user WHERE user_id=' . $gu1->userId . ' AND group_id=' .
						 $gu1->groupId);
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