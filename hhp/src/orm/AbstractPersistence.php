<?php

namespace orm;

/**
 * 抽象的持久化类
 *
 * @author Hoheart
 *        
 */
abstract class AbstractPersistence {

	/**
	 * 保存数据对象，包括了的添加和更新情况，会自己判断是更新还是添加操作。
	 *
	 * @param object $dataObj        	
	 * @param ClassDesc $clsDesc        	
	 * @return 如果是新增，dataObj的主键值将被改写，并返回-1；如果是更新，返回影响的行数；如果该对象不需要保存，则反回0。
	 */
	public function save ($dataObj, ClassDesc $clsDesc = null) {
		if (null == $clsDesc) {
			$clsDesc = DescFactory::Instance()->getDesc(get_class($dataObj));
		}
		
		$status = $this->getPropertyVal($dataObj, 'mDataObjectExistingStatus');
		// 只要开始保存这个对象，就设置保存成功，否则两个类相互引用会死循环。
		$this->setPropertyVal($dataObj, 'mDataObjectExistingStatus', 
				DataClass::DATA_OBJECT_EXISTING_STATUS_SAVED);
		
		if (DataClass::DATA_OBJECT_EXISTING_STATUS_NEW == $status) {
			return $this->add($dataObj, $clsDesc);
		} else if (DataClass::DATA_OBJECT_EXISTING_STATUS_DIRTY == $status) {
			return $this->update($dataObj, $clsDesc);
		} else {
			return 0;
		}
	}

	abstract protected function add ($dataObj, ClassDesc $clsDesc);

	abstract protected function update ($dataObj, ClassDesc $clsDesc);

	protected function setPropertyVal ($dataObj, $attrName, $val) {
		$refCls = new \ReflectionClass(get_class($dataObj));
		$refProperty = $refCls->getProperty($attrName);
		$refProperty->setAccessible(true);
		$refProperty->setValue($dataObj, $val);
	}

	protected function getPropertyVal ($dataObj, $attrName) {
		$refCls = new \ReflectionClass(get_class($dataObj));
		$refProperty = $refCls->getProperty($attrName);
		$refProperty->setAccessible(true);
		return $refProperty->getValue($dataObj);
	}

	/**
	 * 删除指定类的对象数据。
	 *
	 * @param string $className        	
	 * @param Condition $condition        	
	 */
	abstract public function delete ($className, Condition $condition = null);

	/**
	 * 更新是class属性对应的另一个非class属性的值。
	 * 用于保存一个对象时，同时对其属性是class的属性也进行了保存，这时，就需要更新另一个非class的属性。
	 *
	 * @param DataClass $mainObj
	 *        	要更行的对象
	 * @param ClassAttribute $attr
	 *        	对应的属性是class的ClassAttribute
	 * @param DataClass $anotherObj
	 *        	mainObj对应的class属性的值
	 */
	protected function updateForeignKey (DataClass $mainObj, ClassAttribute $attr, $anotherObj) {
		$mainKey = $attr->selfAttribute2Relationship;
		$anotherKey = $attr->anotherAttribute2Relationship;
		$anotherVal = $anotherObj->$anotherKey;
		$mainObj->$mainKey = $anotherVal;
	}
}
?>