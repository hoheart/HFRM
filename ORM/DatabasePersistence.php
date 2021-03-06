<?php

namespace Framework\ORM;

/**
 * 根据AtrributeMap把数据对象持久化到数据库中。
 *
 * @author Hoheart
 *        
 */
class DatabasePersistence extends AbstractPersistence {

	public function __construct () {
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \orm\AbstractPersistence::save()
	 */
	protected function add (DataClass $dataObj, ClassDesc $clsDesc) {
		$keyArr = array();
		$valArr = array();
		$relationArr = array();
		foreach ($clsDesc->attribute as $attrName => $attrObj) {
			if ($attrObj->autoIncrement) {
				continue;
			} else if ('class' == $attrObj->var) {
				$val = $this->getPropertyVal($dataObj, $attrName);
				if (empty($val)) {
					continue;
				}
				
				$tmpValArr = is_array($val) ? $val : array(
					$val
				);
				foreach ($tmpValArr as $oneVal) {
					$this->save($oneVal); // 不管是否是新增，都需要保存关系，因为本对象执行到这儿，肯定是新增，那么关系中就应该新增一条关系
					if (empty($attrObj->relationshipName)) { // 如果是空，说明存放在本类中
						$this->saveSelfAttribute($attrObj, $oneVal, $dataObj, $keyArr, $valArr);
					} else {
						$relationArr[] = array(
							$attrObj,
							$oneVal
						);
					}
				}
				
				continue;
			} else if (empty($attrObj->saveName)) {
				continue;
			}
			
			$v = $this->mDatabaseClient->change2SqlValue($dataObj->$attrName, $attrObj->var);
			$keyArr[] = $attrObj->saveName;
			$valArr[] = $v;
		}
		
		$id = $this->insertIntoDB($clsDesc->saveName, $keyArr, $valArr);
		if ($id > 0 && ! is_array($clsDesc->primaryKey) && $clsDesc->attribute[$clsDesc->primaryKey]->autoIncrement) {
			$onePK = $clsDesc->primaryKey;
			
			self::SetPropertyVal($dataObj, $onePK, $id);
		}
		
		$this->saveRelation($relationArr, $dataObj);
		
		return - 1;
	}

	/**
	 */
	protected function saveSelfAttribute (ClassAttribute $attr, DataClass $anotherObj, DataClass $dataObj, &$keyArr, 
			&$valArr) {
		$attrName = $attr->attribute;
		if (! empty($attrName)) {
			$anotherAttrName = $attr->relationAttribute;
			$attrVal = $anotherObj->$anotherAttrName;
			$dataObj->$attrName = $attrVal;
			
			foreach ($keyArr as $i => $val) {
				if ($attrName == $val) {
					$valArr[$i] = $attrVal;
					
					break;
				}
			}
		} // 如果本类中也不保存这个类的属性，那就没有关系可以保存
	}

	protected function saveRelation ($relationArr, DataClass $dataObj) {
		foreach ($relationArr as $row) {
			list ($attr, $anotherObj) = $row;
			
			$table = $attr->relationshipName;
			$attrName = $attr->attribute;
			$anotherAttrName = $attr->relationAttribute;
			$tableAttrName = $attr->attributeInRelationship;
			$anotherTableAttrName = $attr->relationAttributeInRelationship;
			$anotherVal = $anotherObj->$anotherAttrName;
			if (null == $anotherVal) {
				continue;
			}
			
			$keyArr = array(
				$tableAttrName,
				$anotherTableAttrName
			);
			$valArr = array(
				$dataObj->$attrName,
				$anotherVal
			);
			
			$this->insertIntoDB($table, $keyArr, $valArr);
		}
		
		$this->save($dataObj); // 有可能属性被修改过，重新保存一下，save方法里会判断是否真的需要重新保存。
	}

	protected function insertIntoDB ($tbName, $keyArr, $valArr) {
		$keySql = "`$keyArr[0]`";
		for ($i = 1; $i < count($keyArr); ++ $i) {
			$keySql .= ",`$keyArr[$i]`";
		}
		$sql = 'INSERT INTO `' . $tbName . '` ( ' . $keySql . ' ) VALUES( ' . implode(',', $valArr) . ')';
		$this->mDatabaseClient->exec($sql);
		$id = $this->mDatabaseClient->lastInsertId();
		
		return $id;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \orm\AbstractPersistence::update()
	 */
	protected function update (DataClass $dataObj, ClassDesc $clsDesc) {
		$keyArr = array();
		$valArr = array();
		$pkArr = is_array($clsDesc->primaryKey) ? $clsDesc->primaryKey : array(
			$clsDesc->primaryKey
		);
		foreach ($clsDesc->attribute as $attrName => $attrObj) {
			if (in_array($attrName, $pkArr)) {
				continue;
			} else if ('class' == $attrObj->var) {
				$val = $this->getPropertyVal($dataObj, $attrName);
				if (empty($val)) {
					continue;
				}
				
				$tmpValArr = is_array($val) ? $val : array(
					$val
				);
				foreach ($tmpValArr as $oneVal) {
					$this->save($oneVal);
				}
				
				continue;
			} else if (empty($attrObj->saveName)) {
				continue;
			}
			
			$v = $this->mDatabaseClient->change2SqlValue($dataObj->$attrName, $attrObj->var);
			$keyArr[] = $attrObj->saveName;
			$valArr[] = $v;
		}
		
		$tbName = $clsDesc->saveName;
		$sql = "UPDATE `$tbName` SET `{$keyArr[0]}` = {$valArr[0]} ";
		for ($i = 1; $i < count($keyArr); ++ $i) {
			$sql .= ',';
			$sql .= "`$keyArr[$i]`=$valArr[$i]";
		}
		
		$condArr = array();
		foreach ($pkArr as $k) {
			$dbCol = $clsDesc->attribute[$k]->saveName;
			
			$v = $this->mDatabaseClient->change2SqlValue($dataObj->$k, $clsDesc->attribute[$dbCol]->var);
			$condArr[] = $dbCol . '=' . $v;
		}
		$sql .= ' WHERE ' . implode(' AND ', $condArr);
		
		$ret = $this->mDatabaseClient->exec($sql);
		
		return $ret;
	}

	public function delete ($className, Condition $condition = null) {
		$clsDesc = DescFactory::Instance()->getDesc($className);
		$whereSql = DatabaseFactory::CreateSqlWhere($clsDesc, $condition, $this->mDatabaseClient);
		
		$sql = 'DELETE FROM ' . $clsDesc->saveName;
		if (! empty($whereSql)) {
			$sql .= ' WHERE ' . $whereSql;
		}
		
		return $this->mDatabaseClient->exec($sql);
	}

	/**
	 * 通过ReflectionClass设置属性的值。
	 *
	 * @param DataClass $dataObj        	
	 * @param string $attrName        	
	 * @param mixed $val        	
	 */
	static public function SetPropertyVal ($dataObj, $attrName, $val) {
		$refCls = new \ReflectionClass(get_class($dataObj));
		$refProperty = $refCls->getProperty($attrName);
		$refProperty->setAccessible(true);
		$refProperty->setValue($dataObj, $val);
	}

	/**
	 * 通过ReflectionClass取得属性的值。
	 *
	 * @param DataClass $dataObj        	
	 * @param string $attrName        	
	 * @return mixed
	 */
	protected function getPropertyVal ($dataObj, $attrName) {
		$refCls = new \ReflectionClass(get_class($dataObj));
		$refProperty = $refCls->getProperty($attrName);
		$refProperty->setAccessible(true);
		return $refProperty->getValue($dataObj);
	}
}