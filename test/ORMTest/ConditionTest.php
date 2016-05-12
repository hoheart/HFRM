<?php

namespace test\ORMTest;

use test\AbstractTest;
use orm\Condition;

class ConditionTest extends AbstractTest {

	public function test () {
		$this->construct();
		$this->addChild();
		$this->add();
		$this->equal();
	}

	public function construct () {
		$c = new Condition('a="b"');
		$item = new Condition\Item();
		$item->key = 'a';
		$item->operation = '=';
		$item->value = '"b"';
		
		$conditionItem = $c->itemList[0];
		if ($conditionItem != $item) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function get () {
		$this->construct();
	}

	public function addChild () {
		$c = new Condition('a="b"');
		$item = new Condition\Item();
		
		$c1 = new Condition('a=c');
		$c->addChild($c1);
		
		if ($c->children[0] != $c1) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function add () {
		$c = new Condition('a="b"');
		$item = new Condition\Item();
		$item->key = 'a';
		$item->operation = '=';
		$item->value = 'b';
		
		$c->add('a', '=', 'b');
		
		$conditionItem = $c->itemList[1];
		if ($conditionItem != $item) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function setRelationShip () {
		$c = new Condition('a="b"');
		$c->setRelationship(Condition::RELATIONSHIP_OR);
		
		if (Condition::RELATIONSHIP_OR != $c->relationShip) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function equal () {
		$c = new Condition('a="b"');
		$c->add('b', '>', 'cc');
		
		$c1 = new Condition('c>d');
		$c1->setRelationship(Condition::RELATIONSHIP_OR);
		$c1->add('dd', Condition::OPERATION_LIKE, 'bb');
		$c->addChild($c1);
		
		// 调换一下位置，也应该是相等的。
		$c11 = new Condition('b>cc');
		$c11->add('a', '=', '"b"');
		
		$c12 = new Condition('c>d');
		$c12->setRelationship(Condition::RELATIONSHIP_OR);
		$c12->add('dd', Condition::OPERATION_LIKE, 'bb');
		$c11->addChild($c12);
		
		if (! $c->equal($c11)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}
}
?>