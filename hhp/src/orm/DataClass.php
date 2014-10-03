<?php

namespace icms\Evaluation;

/**
 * 数据类。对于私有的属性，调用__get和__set魔术方法获取和设置值，并构建对象属性的对象。
 *
 * @uses 如果类中有表示另外一个类（记为B）的对象的属性，则把该属性设置成私有或保护的，
 *       那么就会调用__get方法，该方法会调用getAttribute方法，根据B的类描述取得另外一个类的实例，并赋值给该属性。
 * @author Hoheart
 *        
 */
class DataClass {

	public function __get ($name) {
		return $this->getAttribute($name);
	}

	public function __set ($name, $value) {
		return $this->setAttribute($name, $value);
	}

	protected function createFactory () {
		return DatabaseFactory::Instance();
	}

	/**
	 * 取得某个属性。如果属性为Class，要从持久化数据中恢复。
	 *
	 * @param string $name        	
	 */
	public function getAttribute ($name) {
		$val = $this->$name;
		if (null !== $val) {
			return $val;
		}
		
		// 首先取得类的描述
		static $clsDesc = null;
		if (null == $clsDesc) {
			$clsName = get_class($this);
			$clsDesc = DescFactory::Instance()->getDesc($clsName);
			if (null == $clsDesc) {
				throw new \Exception('can not found the class desc. class: ' . $clsName);
			}
		}
		$attr = $clsDesc->attribute[$name];
		if (null == $attr) {
			throw new \Exception('no attribute: ' . $name . ' in this object. class:' . $clsName);
		}
		// 只对属于另外一个类的属性去取值。
		if (! $attr->isClass()) {
			return $val;
		}
		
		$belongClass = $attr->belongClass;
		$factory = $belongClass::getFactory();
		$cond = null;
		$myProp = $attr->selfAttribute2Relationship;
		$myVal = $this->$myProp;
		
		$cond = new Condition();
		if (empty($attr->relationshipName)) { // 空的关系表示:本类的一个属性直接对应另一个累的一个属性。
			$cond->add($attr->anotherAttribute2Relationship, '=', $myVal);
		} else {
			// 首先查询关系
			$relationshipCond[$attr->selfAttributeInRelationship] = $val;
			$relationship = $factory->getDataMapList($attr->relationshipName, $relationshipCond);
			
			foreach ($relationship as $row) {
				$rap = $attr->anotherAttributeInRelationship;
				$ap = $attr->anotherAttribute2Relationship;
				$cond->add($ap, '=', $row[$rap]);
			}
		}
		
		$val = $factory->getDataList($attr->belongClass, $cond);
		
		if (ClassAttribute::VALUE_ATTRIBUTE_SINGLE == $attr->valueAttribute) {
			foreach ($val as $one) {
				$this->$name = $one;
				$val = $one;
				
				break;
			}
		} else {
			$this->$name = $val;
		}
		
		return $val;
	}

	public function setAttribute ($name, $value) {
		$this->$name = $value;
		
		return $this;
	}

	static public function getFactory () {
		static $f = null;
		if (null == $f) {
			$f = new DatabaseFactory();
		}
		
		return $f;
	}
}
?>