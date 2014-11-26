<?php

namespace test\ORMTest;

use test\AbstractTest;
use orm\AbstractPersistence;
use orm\ClassDesc;
use orm\Condition;

class TestAbstractPersistence extends AbstractPersistence {
	public $mRet;

	public function add ($dataObj, $isSaveSub = false, ClassDesc $clsDesc = null) {
		$this->mRet = 'add';
	}

	public function update ($dataObj, $isSaveSub = false, ClassDesc $clsDesc = null) {
		$this->mRet = 'update';
	}

	public function delete ($className, Condition $condition = null) {
	}
}

class AbstractPersistenceTest extends AbstractTest {

	public function test () {
		$this->save();
	}

	public function save () {
		$u = new TestUser();
		$u->name = 'user1';
		
		$p = new TestAbstractPersistence();
		$p->save($u);
		if ($p->mRet != 'add') {
			$this->throwError('', __METHOD__, __LINE__);
		}
		$u->id = 2;
		$p->save($u);
		if ($p->mRet != 'update') {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$gu = new TestGroup2User();
		$p->save($gu);
		if ($p->mRet != 'update') {
			$this->throwError('', __METHOD__, __LINE__);
		}
		$gu->userId = 1;
		$gu->groupId = 2;
		$p->save($gu);
		if ($p->mRet != 'update') {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}
}
?>