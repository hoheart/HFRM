<?php

namespace Framework\ORM;

use Framework\HFC\Exception\MethodCallErrorException;
use Framework\HFC\Database\DatabaseClient;

/**
 * 从数据库中取出各种数据类的工厂类。
 *
 * @author Hoheart
 *        
 */
class DatabaseFactory extends AbstractDataFactory {
	
	/**
	 * 属性为一个数组时，取出的最大数量
	 *
	 * @var integer
	 */
	const MAX_AMOUNT = 50;

	public function __construct () {
	}

	public function get ($className, $id) {
		$clsDesc = DescFactory::Instance()->getDesc($className);
		if (empty($clsDesc->primaryKey) || (is_array($clsDesc->primaryKey) && count($clsDesc->primaryKey) != 1)) {
			throw new MethodCallErrorException('the calss ' . $className . ' does not has one primary key.');
		}
		
		$pk = is_array($clsDesc->primaryKey) ? $clsDesc->primaryKey[0] : $clsDesc->primaryKey;
		$cond = new Condition($pk, '=', $id);
		
		$ret = $this->where($className, $cond, 0, 1, $clsDesc);
		if (is_array($ret) && count($ret) > 0) {
			return $ret[0];
		} else {
			return null;
		}
	}

	/**
	 * 根据Condition 查询一个对象
	 *
	 * @param
	 *        	$className
	 * @param Condition $cond        	
	 * @return mixed
	 */
	public function getOne ($className, Condition $cond) {
		$arr = $this->where($className, $cond, 0, 1);
		if (count($arr) > 0) {
			return $arr[0];
		} else {
			return null;
		}
	}

	public function getRelatedAttribute (ClassAttribute $attr, DataClass $dataObj, ClassDesc $clsDesc) {
		$myProp = $attr->attribute;
		if (empty($myProp)) {
			return null;
		}
		
		$myVal = $dataObj->$myProp;
		
		$amount = 1;
		if ('little' == $attr->amountType || 'large' == $attr->amountType) {
			$amount = 50;
		}
		$val = null;
		if (empty($attr->relationshipName)) { // 空的关系表示:本类的一个属性直接对应另一个累的一个属性。
			$val = $this->where($attr->class, new Condition($attr->relationAttribute, '=', $myVal), 0, $amount);
		} else { // 有关系表记录
			$sqlMyVal = $this->mDatabaseClient->change2SqlValue($myVal, $clsDesc->attribute[$myProp]->var);
			$sql = "SELECT {$attr->relationAttributeInRelationship} FROM {$attr->relationshipName} WHERE {$attr->attributeInRelationship}=$sqlMyVal";
			$ret = $this->mDatabaseClient->select($sql);
			
			$idArr = array();
			foreach ($ret as $row) {
				$idArr[] = $row[$attr->relationAttributeInRelationship];
			}
			
			if (empty($idArr)) {
				return null;
			}
			
			$cond = new Condition($attr->relationAttribute, 'in', $idArr);
			
			$val = $this->where($attr->class, $cond);
		}
		
		if (1 == $amount) {
			if (count($val) > 0) {
				return $val[0];
			} else {
				return null;
			}
		} else {
			return $val;
		}
	}

	public function count ($className, Condition $cond = null) {
		$clsDesc = DescFactory::Instance()->getDesc($className);
		
		$sqlWhere = self::CreateSqlWhere($clsDesc, $cond, $this->mDatabaseClient);
		$sql = 'SELECT COUNT(1) FROM `' . $clsDesc->saveName . '`';
		if (! empty($sqlWhere)) {
			$sql .= ' WHERE ' . $sqlWhere;
		}
		
		$cnt = $this->mDatabaseClient->selectOne($sql, array(), true);
		
		return $cnt;
	}

	public function where ($className, Condition $cond = null, $start = 0, $num = self::MAX_AMOUNT, ClassDesc $clsDesc = null, $order = '', 
			$orderType = 'DESC') {
		if (null == $clsDesc) {
			$clsDesc = DescFactory::Instance()->getDesc($className);
		}
		
		$sqlWhere = self::CreateSqlWhere($clsDesc, $cond, $this->mDatabaseClient);
		$sql = $this->createSqlSelect($clsDesc);
		if (! empty($sqlWhere)) {
			$sql .= 'WHERE ' . $sqlWhere;
		}
		
		if (! empty($order)) {
			$sql .= ' ORDER BY ' . $order . ' ' . $orderType;
		}
		
		$objArr = array();
		// 本来想用PDO::FETCH_CLASS，但如果类的属性值与表的键相同，则不会执行__set函数。
		$ret = $this->mDatabaseClient->select($sql, array(), $start, $num, true);
		foreach ($ret as $row) {
			$createdTime = $row['createdTime'];
			$obj = new $className($createdTime);
			$obj->setFactory($this);
			
			foreach ($clsDesc->attribute as $attrName => $attr) {
				if (empty($attr->saveName)) {
					continue;
				}
				
				if ($attr->autoIncrement) {
					DatabasePersistence::SetPropertyVal($obj, $attrName, $row[$attr->saveName]);
				} else {
					$obj->$attrName = $row[$attr->saveName];
				}
			}
			
			DatabasePersistence::SetPropertyVal($obj, 'mDataObjectExistingStatus', 
					DataClass::DATA_OBJECT_EXISTING_STATUS_SAVED);
			
			$objArr[] = $obj;
		}
		
		return $objArr;
	}

	protected function createSqlSelect (ClassDesc $clsDesc) {
		$sql = 'SELECT ';
		foreach ($clsDesc->attribute as $attr) {
			if ('class' == $attr->var) {
				continue;
			}
			
			$name = $attr->saveName;
			if (empty($name)) {
				continue;
			}
			
			$sql .= "`$name`,";
		}
		$sql[strlen($sql) - 1] = ' ';
		
		$sql .= 'FROM `' . $clsDesc->saveName . '` ';
		
		return $sql;
	}

	/**
	 * 创建where语句，返回时不带‘WHERE’关键字。
	 *
	 * @param Condition $condition        	
	 * @param ClassDesc $clsDesc        	
	 * @return string
	 */
	static public function CreateSqlWhere (ClassDesc $clsDesc, Condition $condition = null, DatabaseClient $db) {
		if (null == $condition) {
			return '';
		}
		
		$condSqlArr = array();
		foreach ($condition->itemList as $item) {
			$attr = $clsDesc->attribute[$item->key];
			
			$key = $attr->saveName;
			if (empty($key)) {
				continue;
			}
			
			$val = $db->change2SqlValue($item->value, $attr->var);
			
			if (Condition::OPERATION_IN == $item->operation || Condition::OPERATION_NOT_IN == $item->operation) {
				$condSqlArr[] = "$key {$item->operation} (" . implode(',', $val) . ')';
			} else {
				$condSqlArr[] = $key . ' ' . $item->operation . ' ' . $val;
			}
		}
		
		foreach ($condition->children as $child) {
			$condSqlArr[] = self::CreateSqlWhere($clsDesc, $child, $db);
		}
		
		$connector = Condition::RELATIONSHIP_OR == $condition->relationship ? ' OR ' : ' AND ';
		$sql = implode($connector, $condSqlArr);
		if (count($condSqlArr) > 1) {
			$sql = "($sql)";
		}
		
		return $sql;
	}
}
