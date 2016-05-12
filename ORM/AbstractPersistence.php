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
	 * @param DataClass $dataObj        	
	 * @param ClassDesc $clsDesc        	
	 * @return 如果是新增，dataObj的主键值将被改写，并返回-1；如果是更新，返回影响的行数；如果该对象不需要保存，则反回0。
	 */
	public function save (DataClass $dataObj, ClassDesc $clsDesc = null) {
		if (null == $clsDesc) {
			$clsDesc = DescFactory::Instance()->getDesc(get_class($dataObj));
		}
		
		$status = $this->getPropertyVal($dataObj, 'mDataObjectExistingStatus');
		// 只要开始保存这个对象，就设置保存成功，否则两个类相互引用会死循环。
		$this->setPropertyVal($dataObj, 'mDataObjectExistingStatus', DataClass::DATA_OBJECT_EXISTING_STATUS_SAVED);
		
		if (DataClass::DATA_OBJECT_EXISTING_STATUS_NEW == $status) {
			return $this->add($dataObj, $clsDesc);
		} else if (DataClass::DATA_OBJECT_EXISTING_STATUS_DIRTY == $status) {
			return $this->update($dataObj, $clsDesc);
		} else {
			return 0;
		}
	}

	/**
	 * 添加操作
	 *
	 * @param DataClass $dataObj        	
	 * @param ClassDesc $clsDesc        	
	 */
	abstract protected function add (DataClass $dataObj, ClassDesc $clsDesc);

	/**
	 * 更新操作
	 *
	 * @param DataClass $dataObj        	
	 * @param ClassDesc $clsDesc        	
	 */
	abstract protected function update (DataClass $dataObj, ClassDesc $clsDesc);

	/**
	 * 删除指定类的对象数据。
	 *
	 * @param string $className        	
	 * @param Condition $condition        	
	 */
	abstract public function delete ($className, Condition $condition = null);
}
?>