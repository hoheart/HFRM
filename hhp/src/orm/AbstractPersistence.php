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
	 * 保存数据对象，包括了大多数的添加和更新情况。
	 * 如果只有一个主键，且有值，视为更新操作，如果主键值为空，视为添加。
	 * 如果是多个主键，统一视为更新。如果想添加，请调用add方法。
	 *
	 * @param object $dataObj        	
	 * @param string $className        	
	 * @param boolean $isSaveSub        	
	 * @param boolean $isDelete        	
	 * @param ClassDesc $clsDesc
	 *        	如果属性是对象，是否保存。
	 * @return 如果是新增，dataObj的主键值将被改写，并返回-1；如果是更新，返回影响的行数。
	 */
	public function save ($dataObj, $isSaveSub = false, ClassDesc $clsDesc = null) {
		if (null == $clsDesc) {
			$clsDesc = DescFactory::Instance()->getDesc(get_class($dataObj));
		}
		
		if (! is_array($clsDesc->primaryKey)) {
			$pk = $clsDesc->primaryKey;
			$pkVal = $dataObj->$pk;
			if (null === $pkVal) {
				return $this->add($dataObj, $isSaveSub, $clsDesc);
			}
		}
		
		return $this->update($dataObj, $isSaveSub, $clsDesc);
	}

	abstract public function add ($dataObj, $isSaveSub = false, ClassDesc $clsDesc = null);

	abstract public function update ($dataObj, $isSaveSub = false, ClassDesc $clsDesc = null);

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