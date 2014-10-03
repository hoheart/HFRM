<?php

namespace ORM;

/**
 * 抽象的持久化类
 *
 * @author Hoheart
 *        
 */
abstract class AbstractPersistence {

	/**
	 * 保存数据对象
	 *
	 * @param object $dataObj        	
	 * @param string $className        	
	 * @param boolean $isSaveSub        	
	 * @param boolean $isDelete        	
	 * @param ClassDesc $clsDesc
	 *        	如果属性是对象，是否保存。
	 */
	abstract public function save ($dataObj, $className = null, $isSaveSub = false, $isDelete = false, 
			ClassDesc $clsDesc = null);

	/**
	 * 删除指定类的对象数据。
	 *
	 * @param string $className        	
	 * @param Condition $condition        	
	 */
	abstract public function delete ($className, Condition $condition = null);

	/**
	 * 创建出要保存的键值对（php数组）。
	 *
	 * @param object $dataObj        	
	 * @param ClassDesc $classDesc        	
	 * @param boolean $isSaveSub        	
	 * @return array 对于只有一个键作为主键的，返回key=>value数组。
	 */
	public function createSaveMap ($dataObj, ClassDesc $classDesc, $isSaveSub = false) {
		$map = array();
		
		foreach ($classDesc->attribute as $attr) {
			$persistentName = $attr->persistentName; // 注意，empty不会调用__get方法
			if (empty($persistentName) && ! $isSaveSub) {
				continue;
			}
			
			$name = $attr->name;
			$val = $dataObj->$name;
			if (ClassAttribute::DATA_TYPE_CLASS == $attr->dataType) {
				if (null == $val || ! $isSaveSub) {
					continue;
				}
				
				// 首先保存关连的另外一个类。
				if (is_array($val)) {
					foreach ($val as $oneObj) {
						$this->save($oneObj, $attr->belongClass, $isSaveSub);
						
						$this->updateForeignKey($map, $dataObj, $oneObj, $classDesc, $name);
					}
				} else {
					$this->save($val, $attr->belongClass, $isSaveSub);
					
					$this->updateForeignKey($map, $dataObj, $oneObj, $classDesc, $name);
				}
				
				// 保存完，在map里就不需要再保留了。
				continue;
			} else if (null === $val && ClassAttribute::ATTRIBUTE_AUTO_INCREMENT == $attr->attribute) {
				$val = Sequence::Instance()->next($classDesc->persistentName);
				$dataObj->$name = $val;
			} else {
				$val = AbstractDataFactory::filterValue($val, $attr->dataType);
			}
			
			$map[$attr->persistentName] = $val;
		}
		
		return $map;
	}

	/**
	 * 保存关系对象。
	 *
	 * @param ClassAttribute $attr        	
	 * @param object $mainObj        	
	 * @param object $anotherObj        	
	 */
	public function saveRelationship (ClassAttribute $attr, $mainObj, $anotherObj) {
		// 关系中的键名
		$rKeyMain = $attr->selfAttributeInRelationship;
		$rKeyAnother = $attr->anotherAttributeInRelationship;
		
		// 各自对象对应的键名
		$keyMain = $attr->selfAttribute2Relationship;
		$keyAnother = $attr->anotherAttribute2Relationship;
		
		// 给关系对象赋值
		$rObj = new stdClass();
		$rObj->$rKeyMain = $mainObj->$keyMain;
		$rObj->$rKeyAnother = $anotherObj->$keyAnother;
		
		$this->save($rObj, $attr->relationshipName);
	}

	protected function updateForeignKey (&$map, $mainObj, $anotherObj, ClassDesc $clsDesc, $keyName) {
		$attr = $clsDesc->attribute[$keyName];
		
		// 更新关系
		if (! empty($attr->relationshipName)) {
			$this->saveRelationship($attr, $mainObj, $anotherObj);
		} else {
			$keyAnother = $attr->anotherAttribute2Relationship;
			$valAnother = $anotherObj->$keyAnother;
			$keySelf = $attr->selfAttribute2Relationship;
			$mainObj->$keySelf = $valAnother;
			
			// 修改已经生成的map里的值
			$attrFKey = $clsDesc->attribute[$keySelf];
			$map[$attrFKey->persistentName] = $valAnother;
		}
	}
}
?>