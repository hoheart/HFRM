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
	
	/**
	 * @hhp:orm var class
	 * @hhp:orm belongClass test\ORMTest\TestGroup
	 * @hhp:orm relationshipName group2user
	 * @hhp:orm selfAttributeInRelationship user_id
	 * @hhp:orm selfAttribute2Relationship id
	 * @hhp:orm anotherAttributeInRelationship group_id
	 * @hhp:orm anotherAttribute2Relationship id
	 *
	 * @var TestGroup
	 */
	protected $group;

	public function setId ($id) {
		$this->id = $id;
	}

	public function getId () {
		return $this->id;
	}

	public function setName ($name) {
		$this->name = $name;
	}

	public function getName () {
		return $this->name;
	}

	public function setAge ($age) {
		$this->age = $age;
	}

	public function getAge () {
		return $this->age;
	}

	public function setAmount ($amount) {
		$this->amount = $amount;
	}

	public function getAmount () {
		return $this->amount;
	}

	public function setBirthday ($day) {
		$this->birthday = $day;
	}

	public function getBirthday () {
		return $this->birthday;
	}

	public function setRegisterTime ($time) {
		$this->registerTime = $time;
	}

	public function getRegisterTime () {
		return $this->registerTime;
	}

	public function setFemale ($female) {
		$this->female = $female;
	}

	public function getFemale () {
		return $this->female;
	}

	public function setGroup ($group) {
		$this->group = $group;
	}

	public function getGroup () {
		return $this->group;
	}
}
?>