<?php

namespace orm;

use orm\exception\ParseClassDescErrorException;

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

	protected function getBaseClassName ($clsName) {
		$pos = strrpos($clsName, '\\');
		if ($pos > 0) {
			return substr($clsName, $pos + 1);
		} else {
			return $clsName;
		}
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
		if (! key_exists('entity', $keyVal)) {
			throw new ParseClassDescErrorException('not a entity.');
		}
		
		if (key_exists('saveName', $keyVal)) {
			$clsDesc->saveName = $keyVal['saveName'];
		} else {
			$clsDesc->saveName = $this->getBaseClassName($className);
		}
		
		$clsDesc->desc = $keyVal['desc'];
		
		if (key_exists('primaryKey', $keyVal)) {
			$primaryKey = explode(',', $keyVal['primaryKey']);
			if (1 == count($primaryKey)) {
				$clsDesc->primaryKey = $primaryKey[0];
			} else {
				$clsDesc->primaryKey = $primaryKey;
			}
		} else {
			$clsDesc->primaryKey = 'id';
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
			
			if (key_exists('saveName', $keyVal)) {
				$attr->saveName = $keyVal['saveName'];
			} else {
				$attr->saveName = $rp->name;
			}
			
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
			if (! empty($attr->saveName)) {
				$clsDesc->saveNameIndexAttr[$attr->saveName] = $attr;
			}
		}
		
		$this->mClassDescMap[$className] = $clsDesc;
		
		return $clsDesc;
	}

	protected function parseDocComment ($doc) {
		if ('' == $doc) {
			return;
		}
		$doc = ltrim(substr($doc, 3, strlen($doc) - 5), ' \t'); // ReflectionClass只支持/**格式的注释
		
		$desc = '';
		$docEnded = false;
		$keyVal = array();
		$var = '';
		$arr = preg_split('/@|(\n[ \t\*]*)/', $doc, - 1, PREG_SPLIT_OFFSET_CAPTURE);
		foreach ($arr as $item) {
			list ($str, $pos) = $item;
			if ('@' == $doc[$pos - 1]) {
				$docEnded = true;
				
				$rowArr = preg_split('/[ \t]/', $str);
				if ('hhp:orm' == $rowArr[0]) {
					$keyVal[$rowArr[1]] = $rowArr[2];
				} else if ('var' == $rowArr[0]) {
					$var = $rowArr[1];
				}
			} else if (! $docEnded) {
				$desc .= $str;
			}
		}
		
		if (! key_exists('var', $keyVal)) {
			$keyVal['var'] = $var;
		}
		if (! key_exists('desc', $keyVal)) {
			$keyVal['desc'] = $desc;
		}
		
		return $keyVal;
	}
}
?>