<?php

namespace Framework\ORM;

use Framework\ORM\Exception\NoPropertyException;
use HFC\Exception\ParameterErrorException;
use Framework\Session;

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
	 *
	 * @var integer
	 */
	protected $terminalType;
	
	/**
	 * 框架用的属性。不能被继承。
	 * @orm saveName
	 *
	 * @var integer
	 */
	protected $mDataObjectExistingStatus = self::DATA_OBJECT_EXISTING_STATUS_NEW;
	
	/**
	 * 框架用的属性。不能被继承。
	 * 主要用于获取关系对象
	 *
	 * @orm saveName
	 *
	 * @var AbstractDataFactory
	 */
	protected $mFactory = null;

	public function __construct ($t = null) {
		$this->setCreatedTime($t);
		$this->setTerminalType();
                $this->setGuid();
	}

	public function __get ($name) {
		return $this->getAttribute($name);
	}

	public function __set ($name, $value) {
		return $this->setAttribute($name, $value);
	}

	protected function getFactory () {
		if (null == $this->mFactory) {
			$c = new DatabaseFactoryCreator();
			$this->mFactory = $c->create(array());
		}
		
		return $this->mFactory;
	}

	public function setFactory ($f) {
		$this->mFactory = $f;
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
			if (null != $attr && $attr->isClass()) {
				$this->$name = $this->getFactory()->getRelatedAttribute($attr, $this, $clsDesc);
			}
		}
		
		$methodName = 'get' . ucfirst($name);
		if (method_exists($this, $methodName)) {
			return $this->$methodName();
		} else {
			if (property_exists($this, $name)) {
				return $this->$name;
			} else {
				throw new NoPropertyException(
						'Property:' . $name . ' not exists in class: ' . get_class($this) . ' or no get mutator defined.');
			}
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
		$val = $this->filterValue($value, $clsDesc->attribute[$name]->var);
		
		$methodName = 'set' . ucfirst($name);
		if (method_exists($this, $methodName)) {
			$this->$methodName($val);
		} else {
			if (property_exists($this, $name)) {
				$this->$name = $val;
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
		}
		
		$this->mDataObjectExistingStatus = self::DATA_OBJECT_EXISTING_STATUS_NEW == $this->mDataObjectExistingStatus ? self::DATA_OBJECT_EXISTING_STATUS_NEW : self::DATA_OBJECT_EXISTING_STATUS_DIRTY;
		
		return $this;
	}

	/**
	 * 根据变量类型，对变量进行过滤
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
			// 由于float和double类型在向int型转换时，会有精度误差，比如如下代码：
			// $val = 1.14 * 100; $a = (int)$val;那么，a的值为113，所以此处先转换成字符串再转换成int
			if ('double' == gettype($val) || 'float' == gettype($val)) {
				$v = (int) ((string) $val);
			} else {
				$v = (int) $val;
			}
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

	protected function setCreatedTime ($t) {
		if (! property_exists($this, 'createdTime')) {
			return;
		}
		
		if (null == $t) {
			$this->createdTime = new \DateTime();
		} else {
			if (is_string($t)) {
				if ('0000-00-00 00:00:00' == $t) {
					$this->createdTime = new \DateTime();
				} else {
					try {
						$this->createdTime = \DateTime::createFromFormat('Y-m-d H:i:s', $t);
					} catch (\Exception $e) {
						throw new ParameterErrorException('DateTime format error.');
					}
				}
			} else if ($t instanceof \DateTime) {
				$this->createdTime = $t;
			} else {
				throw new ParameterErrorException('DateTime format error.');
			}
		}
	}

	public function getCreatedTime () {
		if (! property_exists($this, 'createdTime')) {
			return null;
		}
		
		return $this->createdTime;
	}

	public function setTerminalType () {
		$this->terminalType = Session::Instance()->get('FRM_terminalType');
		
		return $this;
	}
        public function setGuid () {
		$this->guid = uuid_create();
		
		return $this;
	}
	public function __toString () {
		$this->mFactory = null;
		
		$arr = get_object_vars($this);
		
		return json_encode($arr);
	}
}
?>