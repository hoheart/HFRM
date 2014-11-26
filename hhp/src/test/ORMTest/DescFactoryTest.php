<?php

namespace test\ORMTest;

use orm\DescFactory;
use test\AbstractTest;
use orm\ClassDesc;

class DescFactoryTest extends AbstractTest {

	public function test () {
		$this->construct();
		$this->instance();
		$this->getDesc();
	}

	public function construct () {
	}

	public function instance () {
		$df = DescFactory::Instance();
		if (! $df instanceof DescFactory) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function getDesc () {
		$df = DescFactory::Instance();
		$testGroup = new TestGroup();
		$d = $df->getDesc(get_class($testGroup));
		if (! $d instanceof ClassDesc) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		if ($d->desc != '测试用的DataClass' || $d->persistentName !== 'test_group' ||
				 $d->primaryKey != 'id') {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$attr = $d->attribute['userArr'];
		if ('userArr' != $attr->name || $attr->persistentName != '' || '用户对象数组' != $attr->desc ||
				 'class' != $attr->var || false !== $attr->key || false !== $attr->autoIncrement ||
				 'little' != $attr->amountType || $attr->belongClass != 'test\ORMTest\TestUser' ||
				 $attr->relationshipName != 'group2user' ||
				 $attr->selfAttributeInRelationship != 'group_id' ||
				 $attr->selfAttribute2Relationship != 'id' ||
				 $attr->anotherAttributeInRelationship != 'user_id' ||
				 $attr->anotherAttribute2Relationship != 'id') {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		// 如果没写var，默认为string256
		$attr = $d->attribute['name'];
		if ('string256' !== $attr->var) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}
}

?>