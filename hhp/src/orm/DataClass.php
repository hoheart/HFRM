<?php

namespace orm;

use hfc\exception\ParameterErrorException;

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

	protected function getFactory () {
		static $f = null;
		if (null == $f) {
			$c = new DatabaseFactoryCreator();
			$f = $c->create();
		}
		
		return $f;
	}

	/**
	 * 取得某个属性。如果属性为Class，要从持久化数据中恢复。
	 *
	 * @param string $name        	
	 */
	protected function getAttribute ($name) {
		$val = $this->$name;
		if (null !== $val) {
			return $val;
		}
		
		// 首先取得类的描述
		$clsName = get_class($this);
		$clsDesc = DescFactory::Instance()->getDesc($clsName);
		if (null == $clsDesc) {
			throw new \Exception('can not found the class desc. class: ' . $clsName);
		}
		
		$attr = $clsDesc->attribute[$name];
		if (null == $attr) {
			throw new \Exception('no attribute: ' . $name . ' in this object. class:' . $clsName);
		}
		// 只对属于另外一个类的属性去取值。
		if (! $attr->isClass()) {
			return $val;
		}
		
		$myProp = $attr->selfAttribute2Relationship;
		$myVal = $this->$myProp;
		$cond = new Condition($myProp . '=' . $myVal);
		
		$belongClass = $attr->belongClass;
		
		if (empty($attr->relationshipName)) { // 空的关系表示:本类的一个属性直接对应另一个累的一个属性。
			$cond->add($attr->anotherAttribute2Relationship, '=', $myVal);
		} else {
			// 首先查询关系
			$relationshipCond[$attr->selfAttributeInRelationship] = $val;
			$relationship = $this->getFactory()->getDataMapList($attr->relationshipName, 
					$relationshipCond);
			
			foreach ($relationship as $row) {
				$rap = $attr->anotherAttributeInRelationship;
				$ap = $attr->anotherAttribute2Relationship;
				$cond->add($ap, '=', $row[$rap]);
			}
		}
		
		$val = $this->getFactory()->getDataList($attr->belongClass, $cond);
		
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

	protected function setAttribute ($name, $value) {
		$clsDesc = DescFactory::Instance()->getDesc(get_class($this));
		
		if (property_exists($this, $name)) {
			$this->$name = $this->filterValue($value, $clsDesc->attribute[$name]->var);
		} else {
			throw new ParameterErrorException(
					'the property not exists in class: ' . get_class($this));
		}
		
		return $this;
	}

	/**
	 * 根据变量类型，对变量进行过滤
	 * 因为考虑select会比update多，所以，把值的过滤放这个函数里。
	 *
	 * @param object $val        	
	 * @param string $type        	
	 * @return object
	 */
	protected function filterValue ($val, $type) {
		if (is_array($val)) {
			$arr = array();
			foreach ($val as $one) {
				$arr[] = self::filterValue($one, $type);
			}
			
			return $arr;
		}
		
		$v = null;
		if ('string' == substr($type, 0, 6)) {
			$v = (string) $val;
		} else if ('int' == substr($type, 0, 3)) {
			$v = (int) $val;
		} else if ('float' == substr($type, 0, 5)) {
			$v = (float) $val;
		} else {
			switch ($type) {
				case 'date':
					if (! $val instanceof \DateTime) {
						$v = \DateTime::createFromFormat('Y-m-d H:i:s', $val . ' 00:00:00');
					} else {
						$v = $val;
					}
					break;
				case 'time':
					if (! $val instanceof \DateTime) {
						$v = \DateTime::createFromFormat('H:i:s', $val);
					} else {
						$v = $val;
					}
					break;
				case 'datetime':
					if (! $val instanceof \DateTime) {
						$v = \DateTime::createFromFormat('Y-m-d H:i:s', $val);
					} else {
						$v = $val;
					}
					break;
				case 'boolean':
					$v = (boolean) $val;
					break;
				default:
					$v = $val;
					break;
			}
		}
		return $v;
	}
}
?>