<?php

namespace test\ORMTest;

use orm\DataClass;

/**
 * @hhp:orm persistentName test_group2user
 * @hhp:orm desc 测试用的DataClass
 * @hhp:orm primaryKey userId,groupId
 */
class TestGroup2User extends DataClass {
	
	/**
	 * @hhp:orm var int64
	 * @hhp:orm persistentName user_id
	 */
	protected $userId = 1;
	
	/**
	 * @hhp:orm var int32
	 * @hhp:orm persistentName group_id
	 */
	protected $groupId = 2;
	
	/**
	 * @hhp:orm persistentName val
	 */
	protected $val;
}
?>