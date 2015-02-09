<?php

namespace orm;

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
	 * 该类对象的存在状态定义。
	 *
	 * @var integer
	 */
	const DATA_OBJECT_EXISTING_STATUS_NEW = 1; // 新创建的，还没有被修改过。
	const DATA_OBJECT_EXISTING_STATUS_DIRTY = 2; // 脏数据，需要被更新到数据库。
	const DATA_OBJECT_EXISTING_STATUS_SAVED = 3; // 已经被保存，还没有任何属性被修改。
	
	/**
	 * 框架用的属性。不能被继承。
	 * @hhp:orm saveName
	 *
	 * @var integer
	 */
	protected $mDataObjectExistingStatus = self::DATA_OBJECT_EXISTING_STATUS_NEW;

	public function __get ($name) {
		return $this->getAttribute($name);
	}

	public function __set ($name, $value) {
		return $this->setAttribute($name, $value);
	}

	protected function getFactory () {
		$c = new DatabaseFactoryCreator();
		return $c->create(array());
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
			
			$attr = $clsDesc->attribute[$name];
			// 只对属于另外一个类的属性去取值。
			if ($attr->isClass()) {
				$this->$name = $this->getFactory()->getRelatedAttribute($attr, $this, $clsDesc);
			}
		}
		
		$methodName = 'get' . ucfirst($name);
		if (method_exists($this, $methodName)) {
			return $this->$methodName();
		} else {
			throw new NoPropertyException(
					'Property:' . $name . ' not exists in class: ' . get_class($this) . ' or no get mutator defined.');
		}
	}

	protected function setAttribute ($name, $value, $isSaveName = false) {
		if (null === $value) {
			if (property_exists($this, $name)) {
				$this->$name = $value;
				
				return $this;
			}
		}
		
		$clsDesc = DescFactory::Instance()->getDesc(get_class($this));
		
		$methodName = 'set' . ucfirst($name);
		if (method_exists($this, $methodName)) {
			$val = $this->filterValue($value, $clsDesc->attribute[$name]->var);
			$this->$methodName($val);
			
			$this->mDataObjectExistingStatus = self::DATA_OBJECT_EXISTING_STATUS_NEW == $this->mDataObjectExistingStatus ? self::DATA_OBJECT_EXISTING_STATUS_NEW : self::DATA_OBJECT_EXISTING_STATUS_DIRTY;
		} else {
			// 有可能是saveName
			// if (! $isSaveName) {
			// $attr = $clsDesc->saveNameIndexAttr[$name];
			// if (! empty($attr)) {
			// $name = $attr->name;
			// return $this->setAttribute($name, $value, true);
			// }
			// }
			throw new NoPropertyException(
					'Property:' . $name . ' not exists in class: ' . get_class($this) . ' or no set mutator defined.');
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
		$type = strtolower($type);
		
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