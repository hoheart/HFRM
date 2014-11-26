<?php

namespace test\ORMTest;

class TestUser1 extends TestUser {
	
	/**
	 * @hhp:var class;
	 * @hhp:belongClass test\ORMTest\TestGroup
	 * @hhp:anotherAttributeInRelationship groupId
	 * @hhp:anotherAttribute2Relationship id
	 */
	protected $group;
	
	/**
	 * @hhp:var int64
	 */
	protected $groupId;
}
?>