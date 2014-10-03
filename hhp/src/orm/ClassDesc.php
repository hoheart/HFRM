<?php

namespace ORM;

/**
 * 对数据类进行描述的类。
 *
 * @author Hoheart
 *        
 */
class ClassDesc extends DataClass {
	const CLASS_NAME = __CLASS__;
	
	/**
	 * 类名
	 *
	 * @var string
	 */
	protected $name;
	protected $persistentName;
	protected $attributeFilePath;
	protected $attribute;
	protected $desc;
	protected $primaryKey;
}
?>