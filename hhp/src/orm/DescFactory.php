<?php

namespace icms\Evaluation;

/**
 * 产生各种数据类的描述的工厂类。
 *
 * @author Hoheart
 *        
 */
class DescFactory {
	
	/**
	 * 保存数据的文件夹。
	 *
	 * @var string
	 */
	public static $SaveDir = null;

	/**
	 * 取得单一实例
	 *
	 * @return \icms\Evaluation\ClassFactory
	 */
	static public function Instance () {
		static $me = null;
		if (null === $me) {
			$me = new DescFactory();
		}
		
		return $me;
	}

	/**
	 * 根据类名，取得该类的描述
	 *
	 * @param string $className        	
	 * @return \icms\Evaluation\ClassDesc
	 */
	public function getDesc ($className) {
		$clsDesc = null;
		
		if (ClassDesc::CLASS_NAME == $className) {
			$clsDesc = $this->createDescClassDesc();
		} else if (ClassAttribute::CLASS_NAME == $className) {
			$clsDesc = $this->createAttributeClassDesc();
		} else {
			static $phpFactory = null;
			if (null == $phpFactory) {
				$phpFactory = new PhpFactory();
				$phpFactory->setSaveDir(self::$SaveDir);
			}
			
			$cond = new Condition('name=' . $className);
			$clsDesc = $phpFactory->getDataObject(ClassDesc::CLASS_NAME, $cond);
			if (null == $clsDesc) {
				return $clsDesc;
			}
			
			$persistentName = self::getClassBasename($className);
			$attrDesc = $this->createAttributeClassDesc();
			$attrDesc->persistentName = $clsDesc->attributeFilePath;
			$map = $phpFactory->getDataList(ClassAttribute::CLASS_NAME, null, $attrDesc);
			
			$clsDesc->attribute = $map;
		}
		
		return $clsDesc;
	}

	/**
	 * 取得不带名字空间的类名 。
	 *
	 * @param string $clsName        	
	 * @return string
	 */
	static public function getClassBasename ($clsName) {
		$pos = strrpos($clsName, '\\');
		if ($pos < 0) {
			return $clsName;
		}
		
		$bname = substr($clsName, $pos + 1);
		
		return $bname;
	}

	/**
	 * 创建描述类的描述对象。即ClassDesc这个类对应的描述。
	 *
	 * @return array
	 */
	protected function createDescClassDesc () {
		static $clsDesc = null;
		if (null == $clsDesc) {
			$bname = self::getClassBasename(ClassDesc::CLASS_NAME);
			
			$clsDesc = new ClassDesc();
			$clsDesc->name = ClassDesc::CLASS_NAME;
			$clsDesc->persistentName = $bname;
			$clsDesc->attributeFilePath = $bname;
			$clsDesc->primaryKey = array(
				'name'
			);
			
			$clsDesc->attribute = $this->createDescClassAtrribute();
		}
		
		return $clsDesc;
	}

	/**
	 * 创建描述类的属性。
	 *
	 * @return array
	 */
	protected function createDescClassAtrribute () {
		$arr = null;
		
		$propArr = array(
			'name',
			'persistentName',
			'attributeFilePath',
			'attribute',
			'desc',
			'primaryKey'
		);
		
		foreach ($propArr as $prop) {
			$attr = new ClassAttribute();
			$attr->name = $prop;
			$attr->persistentName = $prop;
			$attr->dataType = ClassAttribute::DATA_TYPE_STRING;
			$attr->attribute = ClassAttribute::ATTRIBUTE_COMMON;
			$attr->valueAttribute = ClassAttribute::VALUE_ATTRIBUTE_SINGLE;
			
			$arr[$prop] = $attr;
		}
		
		$arr['name']->attribute = ClassAttribute::ATTRIBUTE_KEY;
		
		$arr['attribute']->persistentName = null;
		$arr['attribute']->belongClass = 'icms\Evaluation\ClassAttribute';
		$arr['attribute']->dataType = ClassAttribute::DATA_TYPE_CLASS;
		$arr['attribute']->valueAttribute = ClassAttribute::VALUE_ATTRIBUTE_LITTLE_ARRAY;
		$arr['attribute']->selfAttribute2Relationship = 'attributeFilePath';
		$arr['attribute']->anotherAttribute2Relationship = 'className';
		
		$arr['primaryKey']->valueAttribute = ClassAttribute::VALUE_ATTRIBUTE_LITTLE_ARRAY;
		
		return $arr;
	}

	/**
	 * 创建属性类的描述。
	 *
	 * @return \icms\Evaluation\ClassDesc
	 */
	public function createAttributeClassDesc () {
		static $clsDesc = null;
		if (null == $clsDesc) {
			$bname = self::getClassBasename(ClassAttribute::CLASS_NAME);
			
			$clsDesc = new ClassDesc();
			$clsDesc->name = ClassAttribute::CLASS_NAME;
			$clsDesc->persistentName = $bname;
			$clsDesc->attributeFilePath = $bname;
			$clsDesc->primaryKey = array(
				'name'
			);
			
			$clsDesc->attribute = $this->createAttributeClassAttribute();
		}
		
		return $clsDesc;
	}

	/**
	 * 创建属性类中，各属性的描述。
	 *
	 * @return array
	 */
	protected function createAttributeClassAttribute () {
		$arr = null;
		
		$propArr = array(
			'name',
			'persistentName',
			'dataType',
			'attribute',
			'valueAttribute',
			'belongClass',
			'relationshipName',
			'selfAttributeInRelationship',
			'selfAttribute2Relationship',
			'anotherAttributeInRelationship',
			'anotherAttribute2Relationship'
		);
		
		foreach ($propArr as $prop) {
			$attr = new ClassAttribute();
			$attr->name = $prop;
			$attr->persistentName = $prop;
			$attr->dataType = ClassAttribute::DATA_TYPE_STRING;
			$attr->attribute = ClassAttribute::ATTRIBUTE_COMMON;
			$attr->valueAttribute = ClassAttribute::VALUE_ATTRIBUTE_SINGLE;
			
			$arr[$prop] = $attr;
		}
		
		$arr['name']->attribute = ClassAttribute::ATTRIBUTE_KEY;
		$arr['dataType']->dataType = ClassAttribute::DATA_TYPE_SMALL_INTEGER;
		$arr['attribute']->dataType = ClassAttribute::DATA_TYPE_SMALL_INTEGER;
		$arr['valueAttribute']->dataType = ClassAttribute::DATA_TYPE_SMALL_INTEGER;
		$arr['valueAttribute']->dataType = ClassAttribute::DATA_TYPE_SMALL_INTEGER;
		
		return $arr;
	}
}

/**
 * 对静态变量进行赋值
 */
DescFactory::$SaveDir = __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
?>