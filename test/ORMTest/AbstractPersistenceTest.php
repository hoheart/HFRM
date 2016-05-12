<?php

namespace test\ORMTest;

use test\AbstractTest;
use orm\AbstractPersistence;
use orm\ClassDesc;
use orm\Condition;
use orm\DataClass;

class TestAbstractPersistence extends AbstractPersistence {
	public $mRet;

	public function add (DataClass $dataObj, ClassDesc $clsDesc = null) {
		$this->mRet = 'add';
	}

	public function update (DataClass $dataObj, ClassDesc $clsDesc = null) {
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
		$u->name = 'user11';
		$p->save($u);
		if ($p->mRet != 'update') {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		if (0 !== $p->save($u)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}
}
?>