<?php

namespace ORM;

/**
 * 对数据类的属性进行描述的类
 *
 * @author Hoheart
 *        
 */
class ClassAttribute extends DataClass {
	const CLASS_NAME = __CLASS__;
	
	/**
	 * 数据类型
	 *
	 * @var integer
	 */
	const DATA_TYPE_STRING = 0;
	const DATA_TYPE_NUMERICAL = 1;
	const DATA_TYPE_SMALL_INTEGER = 2;
	const DATA_TYPE_INTEGER = 3;
	const DATA_TYPE_LONG_INTEGER = 4;
	const DATA_TYPE_FLOAT = 5;
	const DATA_TYPE_FILE = 6;
	const DATA_TYPE_DATE = 7;
	const DATA_TYPE_TIME = 8;
	const DATA_TYPE_DATE_TIME = 9;
	const DATA_TYPE_CLASS = 10;
	const DATA_TYPE_BOOLEAN = 11;
	
	/**
	 * 该属性的特点
	 *
	 * @var integer
	 */
	const ATTRIBUTE_COMMON = 0;
	const ATTRIBUTE_KEY = 1;
	const ATTRIBUTE_AUTO_INCREMENT = 2;
	const VALUE_ATTRIBUTE_SINGLE = 0;
	const VALUE_ATTRIBUTE_LITTLE_ARRAY = 1;
	const VALUE_ATTRIBUTE_LARGE_ARRAY = 2;
	
	/**
	 * 属性名称
	 *
	 * @var string
	 */
	protected $name;
	
	/**
	 * 持久化时使用的名称，比如数据库表的字段名。
	 *
	 * @var string
	 */
	protected $persistentName;
	
	/**
	 * 数据类型。
	 * 0或空：表示该对象value字段为字符串。
	 * 1：numerical
	 * 2：small integer
	 * 3：integer
	 * 4：long integer
	 * 5：float
	 * 6：file，表示value字段为一个相对的路径。
	 * 7:Date，表示日期，界面应相应出现日期选择控件
	 * 8:Time，表示时间，界面应相应出现时间选择控件
	 * 9:DateTime，日期时间，界面出现既能选择日期又能选择时间的控件。
	 * 10：Class，表示value是另一个对象的某个属性
	 *
	 * @var integer
	 */
	protected $dataType;
	
	/**
	 * 该属性的特点
	 * 0或空：普通自有属性
	 * 1：唯一键
	 * 2：自增键
	 *
	 *
	 * @var integer
	 */
	protected $attribute;
	
	/**
	 * 0：单个值
	 * 1：小数组
	 * 2：大数组。界面不显示下拉列表
	 *
	 * @var integer
	 */
	protected $valueAttribute;
	
	/**
	 * 该属性所属的类，即该属性的值是另一个类的一个属性，一般是主键。
	 *
	 * @var string
	 */
	protected $belongClass;
	
	/**
	 * 保存belong_class对应关系的表名（如果是数据库就是表明，如果是文件则可能是文件名，依具体业务而定）。
	 * 如果为空，表示关系直接保存在该类中。一般是一对一或多对一的关系。
	 *
	 * @var string
	 */
	protected $relationshipName;
	
	/**
	 * 用于保存关系的类中自己对应的字段。
	 * 比如User{id,name,age},Group{id,name},关系类User2Group{userId,groupId}，要在用户类中表示所属组，
	 * 那么User类就应该是User{id,name,age,group},group对应的selfAttributeInRelationship是userId，
	 * selfAttribute2Relationship是id，anotherAttributeInRelationship是groupId，
	 * anotherAttribute2Relationship是id。
	 * relationshipAttribute是groupId。
	 *
	 * @var string
	 */
	protected $selfAttributeInRelationship;
	
	/**
	 * 本类中对应于关系中的属性。参见selfAttributeInRelationship属性的说明。
	 *
	 * @var string
	 */
	protected $selfAttribute2Relationship;
	
	/**
	 * 关系中的另一个属性，参见selfAttributeInRelationship属性的说明。
	 *
	 * @var string
	 */
	protected $anotherAttributeInRelationship;
	
	/**
	 * 关系类中对应于关系的属性。参见selfAttributeInRelationship属性的说明。
	 *
	 * @var string
	 */
	protected $anotherAttribute2Relationship;

	public function isKey () {
		return self::ATTRIBUTE_AUTO_INCREMENT == $this->attribute ||
				 self::ATTRIBUTE_KEY == $this->attribute;
	}

	public function isClass () {
		return self::DATA_TYPE_CLASS == $this->dataType;
	}
}
?>