<?php

namespace orm;

/**
 * 对数据类进行描述的类。
 *
 * @author Jejim
 *        
 */
class ClassDesc {
	
	/**
	 * 持久化使用的名字。比如数据库表明，文件名等。
	 *
	 * @var string
	 */
	public $saveName;
	
	/**
	 * ClassAttribute的map，key为属性名称。
	 *
	 * @var array
	 */
	public $attribute;
	
	/**
	 * 存储名作索引的属性列表
	 * 
	 * @var array
	 */
	public $saveNameIndexAttr;
	
	/**
	 * 类的描述
	 *
	 * @var string
	 */
	public $desc;
	
	/**
	 * 主键。用逗号分隔多个值。
	 * 如果是一个值，ClassDesc该属性的值为字符串；如果是多个值，为数组。
	 *
	 * @var array string
	 */
	public $primaryKey;
}
?>