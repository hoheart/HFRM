<?php

namespace test\ORMTest;

use orm\DataClass;

/**
 * @hhp:orm persistentName test_group
 * @hhp:orm desc 测试用的DataClass
 * @hhp:orm primaryKey id
 */
class TestGroup extends DataClass {
	
	/**
	 * @hhp:orm persistentName id
	 * @hhp:orm desc id
	 * @hhp:orm var int64
	 * @hhp:orm autoIncrement true
	 */
	protected $id;
	
	/**
	 * @hhp:orm persistentName
	 * @hhp:orm desc 用户对象数组
	 * @hhp:orm var class
	 * @hhp:orm key false
	 * @hhp:orm amountType little
	 * @hhp:orm belongClass test\ORMTest\TestUser
	 * @hhp:orm relationshipName group2user
	 * @hhp:orm selfAttributeInRelationship group_id
	 * @hhp:orm selfAttribute2Relationship id
	 * @hhp:orm anotherAttributeInRelationship user_id
	 * @hhp:orm anotherAttribute2Relationship id
	 */
	protected $userArr;
	
	/**
	 * @hhp:orm persistentName name
	 */
	protected $name;
	
	/**
	 * @hhp:orm persistentName val_string
	 * @hhp:orm desc val string
	 */
	protected $valString = 33;
	
	/**
	 * @hhp:orm persistentName val_float
	 * @hhp:orm desc val string
	 * @hhp:orm dataType 5
	 */
	protected $valFloat = '3.1415';
}
?>