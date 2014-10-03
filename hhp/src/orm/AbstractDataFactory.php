<?php

namespace ORM;

/**
 * 抽象的数据工厂
 *
 * @author Hoheart
 *        
 */
abstract class AbstractDataFactory {

	/**
	 * 取得单一实例
	 */
	abstract static public function Instance ();

	/**
	 * 不用把数据对象转成对象格式，直接返回取得数组。为了保持数据的顺序，不用主键作索引。
	 *
	 * @param string $className        	
	 * @param Condition $condition        	
	 * @param ClassDesc $clsDesc        	
	 * @return array
	 */
	abstract public function getDataMapList ($className, Condition $condition = null, 
			ClassDesc $clsDesc = null);

	/**
	 * 根据数据类的名称，取得对象列表。
	 *
	 * @param string $className        	
	 * @param Condition $condition        	
	 * @param ClassDesc $desc        	
	 * @return array
	 */
	public function getDataList ($className, Condition $condition = null, ClassDesc $desc = null) {
		$objArr = array();
		
		// 1.要取得一个类的对象，先根据其名字，取得类描述。
		$clsDesc = $desc;
		if (null == $clsDesc) {
			$descFactory = DescFactory::Instance();
			$clsDesc = $descFactory->getDesc($className);
		}
		
		// 2.读取成数组
		$arr = $this->getDataMapList($className, $condition, $clsDesc);
		
		// 3.转换成对象。
		$hasOnePKey = 1 == count($clsDesc->primaryKey) ? true : false;
		foreach ($arr as $row) {
			$obj = $row;
			if (! is_object($obj)) {
				$obj = $this->createDataObject($className, $row, $clsDesc);
			}
			
			if ($hasOnePKey) {
				$pkey = $clsDesc->primaryKey[0];
				$pkeyVal = $obj->$pkey;
				$objArr[$pkeyVal] = $obj;
			} else {
				$objArr[] = $obj;
			}
		}
		
		return $objArr;
	}

	/**
	 * 根据valueRow的值创建DataClass对象。返回的obj就是className的对象，
	 * 用valueRow根据attributeMap记录的属性对obj的成员变量赋值。
	 * 注意：在valueRow里，key是persistentName，而对象里是name。
	 *
	 * @param string $className        	
	 * @param array $valueRow        	
	 * @param ClassDesc $clsDesc        	
	 */
	public function createDataObject ($className, array $valueRow, ClassDesc $clsDesc = null) {
		$obj = new $className();
		
		$this->initDataObject($obj, $valueRow, $clsDesc);
		
		return $obj;
	}

	/**
	 * 初始化DataObject，即给每个属性赋值。根据DataObject的键的已有值，取得其他值。
	 *
	 * @param object $dataObj        	
	 * @param array $valueRow        	
	 * @param ClassDesc $clsDesc        	
	 */
	public function initDataObject ($dataObj, array $valueRow, ClassDesc $clsDesc = null) {
		if (empty($clsDesc)) {
			foreach ($valueRow as $key => $val) {
				if (property_exists($dataObj, $key)) {
					$dataObj->$key = $val;
				}
			}
		} else {
			$attributeMap = $clsDesc->attribute;
			
			foreach ($attributeMap as $attr) {
				$attrName = $attr->name;
				$valueRowKeyName = $attr->persistentName;
				$val = self::filterValue($valueRow[$valueRowKeyName], $attr->dataType);
				$dataObj->$attrName = $val;
			}
		}
	}

	/**
	 * 根据条件，只取出一个对象。
	 *
	 * @param string $className        	
	 * @param Condition $condition        	
	 * @return DataClass
	 */
	public function getDataObject ($className, Condition $condition = null) {
		$l = $this->getDataList($className, $condition);
		// 最好不用array_shift，因为他会改变数组，而不是引用。
		foreach ($l as $one) {
			return $one;
		}
		
		return null;
	}

	/**
	 * 根据变量类型，对变量进行过滤
	 *
	 * @param unknown $val        	
	 * @param unknown $type        	
	 * @return multitype:Ambigous <NULL, unknown, multitype:Ambigous <NULL,
	 *         unknown> > |Ambigous <NULL, unknown>
	 */
	static public function filterValue ($val, $type) {
		if (is_array($val)) {
			$arr = array();
			foreach ($val as $one) {
				$arr[] = self::filterValue($one, $type);
			}
			
			return $arr;
		}
		
		$v = null;
		switch ($type) {
			case ClassAttribute::DATA_TYPE_STRING:
			case ClassAttribute::DATA_TYPE_FILE:
				$v = (string) $val;
				break;
			case ClassAttribute::DATA_TYPE_NUMERICAL:
			case ClassAttribute::DATA_TYPE_SMALL_INTEGER:
			case ClassAttribute::DATA_TYPE_INTEGER:
			case ClassAttribute::DATA_TYPE_LONG_INTEGER:
				$v = (int) $val;
				break;
			case ClassAttribute::DATA_TYPE_FLOAT:
				$v = (float) $val;
				break;
			case ClassAttribute::DATA_TYPE_DATE:
			case ClassAttribute::DATA_TYPE_TIME:
			case ClassAttribute::DATA_TYPE_DATE_TIME:
				$v = Datetime::createFromFormat('Y-m-d H:i:s', $val);
				break;
			case ClassAttribute::DATA_TYPE_CLASS:
				$v = null;
				break;
			case ClassAttribute::DATA_TYPE_BOOLEAN:
				$v = (boolean) $val;
				break;
			default:
				$v = $val;
				break;
		}
		
		return $v;
	}

	/**
	 * 取得getDataMapList函数返回的第一个值。
	 *
	 * @param string $className        	
	 * @param Condition $condition        	
	 * @param ClassDesc $clsDesc        	
	 * @return array
	 */
	public function getDataMap ($className, Condition $condition = null, ClassDesc $clsDesc = null) {
		$l = $this->getDataMapList($className, $condition);
		// 最好不用array_shift，因为他会改变数组，而不是引用。
		foreach ($l as $one) {
			return $one;
		}
		
		return null;
	}
}
?>