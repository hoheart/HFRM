<?php

namespace orm;

/**
 * 产生各种数据类的描述（即产生数据类的ClassDesc类）的工厂类。
 *
 * @author Hoheart
 *        
 */
class DescFactory {
	
	/**
	 * 存放类描述的map，如果已经解析过的类，就不重复解析。
	 *
	 * @var array
	 */
	protected $mClassDescMap = array();

	public function __construct () {
	}

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

	public function getDesc ($className) {
		$clsDesc = $this->mClassDescMap[$className];
		if (! empty($clsDesc)) {
			return $clsDesc;
		}
		
		$rc = new \ReflectionClass($className);
		
		// 取得类的描述。
		$doc = $rc->getDocComment();
		$keyVal = $this->parseDocComment($doc);
		$clsDesc = new ClassDesc();
		$clsDesc->persistentName = $keyVal['persistentName'];
		$clsDesc->desc = $keyVal['desc'];
		$primaryKey = explode(',', $keyVal['primaryKey']);
		if (1 == count($primaryKey)) {
			$clsDesc->primaryKey = $primaryKey[0];
		} else {
			$clsDesc->primaryKey = $primaryKey;
		}
		
		// 取得每个属性的描述
		$attrNameArr = $rc->getProperties(
				\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PROTECTED);
		foreach ($attrNameArr as $rp) {
			$doc = $rp->getDocComment();
			$keyVal = $this->parseDocComment($doc);
			
			$attr = new ClassAttribute();
			$attr->name = $rp->getName();
			$attr->desc = $keyVal['desc'];
			
			$attr->var = $keyVal['var'];
			if (empty($attr->var)) {
				$attr->var = 'string256';
			}
			
			$attr->persistentName = $keyVal['persistentName'];
			$attr->key = 'true' == $keyVal['key'];
			$attr->autoIncrement = 'true' == $keyVal['autoIncrement'];
			$attr->amountType = $keyVal['amountType'];
			$attr->belongClass = $keyVal['belongClass'];
			$attr->relationshipName = $keyVal['relationshipName'];
			$attr->selfAttributeInRelationship = $keyVal['selfAttributeInRelationship'];
			$attr->selfAttribute2Relationship = $keyVal['selfAttribute2Relationship'];
			$attr->anotherAttributeInRelationship = $keyVal['anotherAttributeInRelationship'];
			$attr->anotherAttribute2Relationship = $keyVal['anotherAttribute2Relationship'];
			
			$clsDesc->attribute[$rp->getName()] = $attr;
			if (! empty($attr->persistentName)) {
				$clsDesc->persistentNameIndexAttr[$attr->persistentName] = $attr;
			}
		}
		
		$this->mClassDescMap[$className] = $clsDesc;
		
		return $clsDesc;
	}

	protected function parseDocComment ($doc) {
		$keyVal = array();
		$tag = '@hhp:orm';
		$pos = $pos1 = 0;
		while (true) {
			$pos = strpos($doc, $tag, $pos1);
			if (false === $pos) {
				break;
			}
			$pos += strlen($tag) + 1;
			$pos1 = strpos($doc, "\n", $pos);
			if (false === $pos1) {
				$pos1 = strpos($doc, '*/', $pos);
			}
			$row = substr($doc, $pos, $pos1 - $pos);
			$row = trim($row);
			$rowArr = preg_split('/ +/', $row);
			$keyVal[$rowArr[0]] = trim($rowArr[1]);
		}
		
		return $keyVal;
	}
}
?>