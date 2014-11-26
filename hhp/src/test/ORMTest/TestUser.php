<?php

namespace test\ORMTest;

use orm\DataClass;

/**
 * @hhp:orm persistentName test_user
 * @hhp:orm desc 测试用的DataClass
 * @hhp:orm primaryKey id
 */
class TestUser extends DataClass {
	
	/**
	 * @hhp:orm persistentName id
	 * @hhp:orm desc id
	 * @hhp:orm var int64
	 * @hhp:orm autoIncrement true
	 */
	protected $id;
	
	/**
	 * @hhp:orm persistentName name
	 * @hhp:orm desc 分组对象
	 * @hhp:orm var string32
	 */
	protected $name;
	
	/**
	 * @hhp:orm persistentName age
	 * @hhp:orm desc 年龄
	 * @hhp:orm var int4
	 */
	protected $age;
	
	/**
	 * @hhp:orm persistentName amount
	 * @hhp:orm desc 现金账户余额
	 * @hhp:orm var float
	 */
	protected $amount;
	
	/**
	 * @hhp:orm persistentName birthday
	 * @hhp:orm desc 生日
	 * @hhp:orm var date
	 */
	protected $birthday;
	
	/**
	 * @hhp:orm persistentName register_time
	 * @hhp:orm desc 注册时间
	 * @hhp:orm var datetime
	 */
	protected $registerTime;
	
	/**
	 * @hhp:orm persistentName female
	 * @hhp:orm desc 是否是女性
	 * @hhp:orm var boolean
	 */
	protected $female;
}
?>