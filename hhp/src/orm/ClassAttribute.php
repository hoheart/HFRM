<?php

namespace orm;

/**
 * 对数据类的属性进行描述的类
 *
 * @author Hoheart
 *        
 */
class ClassAttribute {
	
	/**
	 * 属性的名称，这个由orm框架填充。
	 *
	 * @var string
	 */
	public $name;
	
	/**
	 * 属性的描述
	 *
	 * @var string
	 */
	public $desc;
	
	/**
	 * 数据类型。
	 * 空：256个字符长度的字符串。
	 * stringN：N个字符长度的字符串。
	 * intN：N位（十进制）长度的整数，比如int2表示0到99的整数。
	 * floatN.N：小数点前N位（十进制），小数点后N位（十进制）的浮点数。
	 * date：表示日期，格式为：YYYY-MM-DD。
	 * time：表示时间，格式为：H24:MI:SS。
	 * datetime：表示日期时间。格式为：YYYY-MM-DD H24:MI:SS。
	 * boolean:true表示真，false表示假
	 * class：表示value是另一个对象的某个属性
	 *
	 * @var string
	 */
	public $var;
	
	/**
	 * 持久化时使用的名称，比如数据库表的字段名。
	 *
	 * @var string
	 */
	public $persistentName;
	
	/**
	 * 是否是键，但不一定是主键。
	 * true：是键
	 * 空或false：不是键
	 *
	 * @var string
	 */
	public $key;
	
	/**
	 * 是否自增长（步长为1）
	 * true：自增长，只能是整形数据和key才能自增长
	 * 空或false：不自增长
	 *
	 * @var string
	 */
	public $autoIncrement;
	
	/**
	 * 数量类型
	 * 空或single：单个值
	 * little：100以内的集合
	 * large：数量没有限制
	 *
	 * @var string
	 */
	public $amountType;
	
	/**
	 * 该属性所属的类，即该属性的值是另一个类的一个属性，一般是主键。
	 *
	 * @var string
	 */
	public $belongClass;
	
	/**
	 * 保存belong_class对应关系的表名（如果是数据库就是表名，如果是文件则可能是文件名，依具体业务而定）。
	 * 如果为空，表示关系直接保存在该类中，一般是一对一或多对一的关系。
	 *
	 * @var string
	 */
	public $relationshipName;
	
	/**
	 * 关系类中自己对应的字段。
	 * 比如User{id,name,age},Group{id,name},关系类User2Group{userId,groupId}，要在用户类中表示所属组，
	 * 那么User类就应该是User{id,name,age,group},group对应的selfAttributeInRelationship是userId，
	 * selfAttribute2Relationship是id，anotherAttributeInRelationship是groupId，
	 * anotherAttribute2Relationship是Group对应的id。
	 *
	 * @var string
	 */
	public $selfAttributeInRelationship;
	
	/**
	 * 本类中的属性，对应于关系中的属性。参见selfAttributeInRelationship属性的说明。
	 *
	 * @var string
	 */
	public $selfAttribute2Relationship;
	
	/**
	 * 关系中的另一个属性，参见selfAttributeInRelationship属性的说明。
	 *
	 * @var string
	 */
	public $anotherAttributeInRelationship;
	
	/**
	 * 关系类中对应于关系的属性。参见selfAttributeInRelationship属性的说明。
	 *
	 * @var string
	 */
	public $anotherAttribute2Relationship;

	public function isClass () {
		return 'class' == $this->var;
	}
}
?>