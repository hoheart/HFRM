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
	protected function add ($dataObj, ClassDesc $clsDesc) {
		$keyArr = array();
		$valArr = array();
		foreach ($clsDesc->attribute as $attrName => $attrObj) {
			if ($attrObj->autoIncrement || empty($attrObj->persistentName)) {
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
			}
			
			$v = $this->mDatabaseClient->change2SqlValue($dataObj->$attrName, $attr->var);
			$keyArr[] = $attrObj->persistentName;
			$valArr[] = $v;
		}
		
		$tbName = $clsDesc->persistentName;
		$sql = 'INSERT INTO ' . $tbName . '( ' . implode(',', $keyArr) . ' ) VALUES( ' .
				 implode(',', $valArr) . ')';
		$statment = $this->mDatabaseClient->query($sql);
		$id = $statment->lastInsertId();
		if ($id > 0 && ! is_array($clsDesc->primaryKey) &&
				 $clsDesc->attribute[$clsDesc->primaryKey]->autoIncrement) {
			$onePK = $clsDesc->primaryKey;
			
			$this->setPropertyVal($dataObj, $onePK, $id);
		}
		
		return - 1;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \orm\AbstractPersistence::update()
	 */
	protected function update ($dataObj, ClassDesc $clsDesc) {
		$keyArr = array();
		$valArr = array();
		foreach ($clsDesc->attribute as $attrName => $attrObj) {
			if (empty($attrObj->persistentName)) {
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
			}
			
			$v = $this->mDatabaseClient->change2SqlValue($dataObj->$attrName, $attr->var);
			$keyArr[] = $attrObj->persistentName;
			$valArr[] = $v;
		}
		
		$tbName = $clsDesc->persistentName;
		$sql = "UPDATE $tbName SET {$keyArr[0]} = {$valArr[0]} ";
		for ($i = 1; $i < count($keyArr); ++ $i) {
			$sql .= ',';
			$sql .= $keyArr[$i] . '=' . $valArr[$i];
		}
		
		$pkArr = $clsDesc->primaryKey;
		if (! is_array($pkArr)) {
			$pkArr = array(
				$clsDesc->primaryKey
			);
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