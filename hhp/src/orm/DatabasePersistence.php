<?php

namespace orm;

use hfc\database\DatabaseClient;

/**
 * 根据AtrributeMap把数据对象持久化到数据库中。
 *
 * @author Hoheart
 *        
 */
class DatabasePersistence extends AbstractPersistence {
	
	/**
	 * 数据库客户端
	 *
	 * @var DatabaseClient
	 */
	protected $mDatabaseClient = null;

	public function __construct () {
	}

	/**
	 * 设置数据库客户端。
	 *
	 * @param DatabaseClient $dbClient        	
	 */
	public function setDatabaseClient (DatabaseClient $dbClient) {
		$this->mDatabaseClient = $dbClient;
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
					if (empty($attr->relationshipName)) { // 如果是空，说明存放在本类中
						$this->saveSelfAttribute($attrObj, $oneVal, $dataObj, $keyArr, $valArr);
					} else {
						$relationArr[] = array(
							$attrObj,
							$oneVal
						);
					}
				}
				
				continue;
			} else if (empty($attrObj->persistentName)) {
				continue;
			}
			
			$v = $this->mDatabaseClient->change2SqlValue($dataObj->$attrName, $attrObj->var);
			$keyArr[] = $attrObj->persistentName;
			$valArr[] = $v;
		}
		
		$id = $this->insertIntoDB($clsDesc->persistentName, $keyArr, $valArr);
		if ($id > 0 && ! is_array($clsDesc->primaryKey) && $clsDesc->attribute[$clsDesc->primaryKey]->autoIncrement) {
			$onePK = $clsDesc->primaryKey;
			
			$this->setPropertyVal($dataObj, $onePK, $id);
		}
		
		$this->saveRelation($relationArr, $dataObj);
		
		return - 1;
	}

	/**
	 */
	protected function saveSelfAttribute (ClassAttribute $attr, DataClass $anotherObj, DataClass $dataObj, &$keyArr, 
			&$valArr) {
		$attrName = $attr->selfAttribute2Relationship;
		if (! empty($attrName)) {
			$anotherAttrName = $attr->anotherAttribute2Relationship;
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
			$attrName = $attr->selfAttribute2Relationship;
			$anotherAttrName = $attr->anotherAttribute2Relationship;
			$tableAttrName = $attr->selfAttributeInRelationship;
			$anotherTableAttrName = $attr->anotherAttributeInRelationship;
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
		$sql = 'INSERT INTO ' . $tbName . '( ' . implode(',', $keyArr) . ' ) VALUES( ' . implode(',', $valArr) . ')';
		$statment = $this->mDatabaseClient->query($sql);
		$id = $statment->lastInsertId();
		
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
			} else if (empty($attrObj->persistentName)) {
				continue;
			}
			
			$v = $this->mDatabaseClient->change2SqlValue($dataObj->$attrName, $attrObj->var);
			$keyArr[] = $attrObj->persistentName;
			$valArr[] = $v;
		}
		
		$tbName = $clsDesc->persistentName;
		$sql = "UPDATE $tbName SET {$keyArr[0]} = {$valArr[0]} ";
		for ($i = 1; $i < count($keyArr); ++ $i) {
			$sql .= ',';
			$sql .= $keyArr[$i] . '=' . $valArr[$i];
		}
		
		$condArr = array();
		foreach ($pkArr as $k) {
			$dbCol = $clsDesc->attribute[$k]->persistentName;
			$condArr[] = $dbCol . '=' . $dataObj->$k;
		}
		$sql .= ' WHERE ' . implode(' AND ', $condArr);
		
		$ret = $this->mDatabaseClient->exec($sql);
		
		return $ret;
	}

	public function delete ($className, Condition $condition = null) {
		$clsDesc = DescFactory::Instance()->getDesc($className);
		$whereSql = DatabaseFactory::CreateSqlWhere($clsDesc, $condition, $this->mDatabaseClient);
		
		$sql = 'DELETE FROM ' . $clsDesc->persistentName;
		if (! empty($whereSql)) {
			$sql .= ' WHERE ' . $whereSql;
		}
		
		$this->mDatabaseClient->exec($sql);
	}
}
?>