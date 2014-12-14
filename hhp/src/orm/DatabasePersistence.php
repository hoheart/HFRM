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
	public function add ($dataObj, $isSaveSub = false, ClassDesc $clsDesc = null) {
		if ($this->getPropertyVal($dataObj, 'mSaved')) {
			return - 1;
		}
		// 只要开始保存这个对象，就设置保存成功，否则会死循环。
		$this->setPropertyVal($dataObj, 'mSaved', true);
		
		if (null == $clsDesc) {
			$clsDesc = DescFactory::Instance()->getDesc(get_class($dataObj));
		}
		
		$tbName = $clsDesc->persistentName;
		
		list ($keyArr, $valArr) = $this->change2SqlValue($dataObj, $clsDesc->attribute, $isSaveSub);
		
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
	public function update ($dataObj, $isSaveSub = false, ClassDesc $clsDesc = null) {
		if ($this->getPropertyVal($dataObj, 'mSaved')) {
			return - 1;
		}
		// 只要开始保存这个对象，就设置保存成功，否则会死循环。
		$this->setPropertyVal($dataObj, 'mSaved', true);
		
		if (null == $clsDesc) {
			$clsDesc = DescFactory::Instance()->getDesc(get_class($dataObj));
		}
		
		$tbName = $clsDesc->persistentName;
		
		list ($keyArr, $valArr) = $this->change2SqlValue($dataObj, $clsDesc->attribute, $isSaveSub, 
				false);
		
		$sql = 'UPDATE ' . $tbName . ' SET ';
		for ($i = 0; $i < count($keyArr); ++ $i) {
			if (0 != $i) {
				$sql .= ',';
			}
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

	/**
	 * 根据属性arr，把dataObj转换成sql语句的值，并返回两个数组。
	 *
	 * @param DataClass $dataObj        	
	 * @param array $attrArr        	
	 * @param array $pk        	
	 * @param boolean $isSaveSub        	
	 * @param boolean $isAdd
	 *        	是否是添加操作
	 * @return array 第一个值为数组，是所有要保存的键，第二个数组为键对应的值，两个数组顺序对应。
	 */
	public function change2SqlValue ($dataObj, $attrArr, $isSaveSub, $isAdd = true) {
		$keyArr = array();
		$valArr = array();
		
		foreach ($attrArr as $attrName => $attrObj) {
			if ($attrObj->autoIncrement) {
				continue;
			} else if ('class' == $attrObj->var) {
				if ($isSaveSub) {
					$val = $this->getPropertyVal($dataObj, $attrName);
					if (empty($val)) {
						continue;
					}
					
					$tmpValArr = is_array($val) ? $val : array(
						$val
					);
					foreach ($tmpValArr as $oneVal) {
						if ($isAdd) {
							$this->add($oneVal, $isSaveSub);
						} else {
							$this->update($oneVal, $isSaveSub);
						}
					}
				}
				
				continue;
			} else if (empty($attrObj->persistentName)) {
				continue;
			}
			
			$val = $dataObj->$attrName;
			$type = $attrObj->var;
			
			if ('class' != $type) {
				$v = $this->mDatabaseClient->change2SqlValue($val, $type);
				
				$keyArr[] = $attrObj->persistentName;
				$valArr[] = $v;
			}
		}
		
		return array(
			$keyArr,
			$valArr
		);
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