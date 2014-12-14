<?php

namespace orm;

use hfc\exception\ParameterErrorException;
use hhp\App;
use orm\exception\ParseClassDescErrorException;
use orm\exception\NoPropertyException;

/**
 * 数据类。对于私有的属性，调用__get和__set魔术方法获取和设置值，并构建对象属性的对象。
 *
 * @uses 如果类中有表示另外一个类（记为B）的对象的属性，则把该属性设置成私有或保护的，
 *       那么就会调用__get方法，该方法会调用getAttribute方法，根据B的类描述取得另外一个类的实例，并赋值给该属性。
 * @author Hoheart
 *        
 */
class DataClass {
	
	/**
	 * 是否被保存过。如果一旦某个属性被修改了，就不是true了。
	 *
	 * @var boolean
	 */
	protected $mSaved = false;

	public function __get ($name) {
		return $this->getAttribute($name);
	}

	public function __set ($name, $value) {
		return $this->setAttribute($name, $value);
	}

	protected function getFactory () {
		return App::Instance()->getService('orm');
	}

	/**
	 * 取得某个属性。如果属性为Class，要从持久化数据中恢复。
	 *
	 * @param string $name        	
	 */
	protected function getAttribute ($name) {
		$val = $this->$name;
		if (null === $val) {
			// 首先取得类的描述
			$clsName = get_class($this);
			$clsDesc = DescFactory::Instance()->getDesc($clsName);
			if (null == $clsDesc) {
				throw new ParseClassDescErrorException(
						'can not parse class desc for class:' . $clsName);
			}
			
			$attr = $clsDesc->attribute[$name];
			// 只对属于另外一个类的属性去取值。
			if ($attr->isClass()) {
				$f = $this->getFactory();
				
				$myProp = $attr->selfAttribute2Relationship;
				$myVal = $this->$myProp;
				
				if (empty($attr->relationshipName)) { // 空的关系表示:本类的一个属性直接对应另一个累的一个属性。
					$cond = new Condition();
					$cond->add($attr->anotherAttribute2Relationship, '=', $myVal);
					
					$val = $f->getDataMapList($attr->belongClass, $cond);
				} else { // 有关系表记录
					$val = $f->getDataMapListFromRelation($clsDesc, $name, $myVal);
				}
				
				$tmpVal = null;
				$belongClsDesc = DescFactory::Instance()->getDesc($attr->belongClass);
				$cls = $attr->belongClass;
				foreach ($val as $row) {
					$obj = new $cls();
					foreach ($belongClsDesc->attribute as $belongClsAttr) {
						$attrName = $belongClsAttr->name;
						$obj->$attrName = $row[$belongClsAttr->persistentName];
					}
					
					if ('single' == $attr->amountType) {
						$tmpVal = $obj;
						break;
					} else {
						$tmpVal[] = $obj;
					}
				}
				
				$this->$name = $tmpVal;
			}
		}
		
		$methodName = 'get' . ucfirst($name);
		if (method_exists($this, $methodName)) {
			return $this->$methodName();
		} else {
			throw new NoPropertyException(
					'Property:' . $name . ' not exists in class: ' . get_class($this) .
							 ' or no get mutator defined.');
		}
	}

	protected function setAttribute ($name, $value) {
		$clsDesc = DescFactory::Instance()->getDesc(get_class($this));
		
		$methodName = 'set' . ucfirst($name);
		if (method_exists($this, $methodName)) {
			$val = $this->filterValue($value, $clsDesc->attribute[$name]->var);
			$this->$methodName($val);
			
			$this->mSaved = false;
		} else {
			throw new NoPropertyException(
					'Property:' . $name . ' not exists in class: ' . get_class($this) .
							 ' or no set mutator defined.');
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