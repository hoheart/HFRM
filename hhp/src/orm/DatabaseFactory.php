<?php

namespace orm;

use hfc\database\DatabaseClient;
use hfc\exception\MethodCallErrorException;

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
	
	/**
	 *
	 * @var DatabaseClient
	 */
	protected $mDatabaseClient = null;

	public function __construct () {
	}

	public function setDatabaseClient (DatabaseClient $client) {
		$this->mDatabaseClient = $client;
	}

	public function get ($className, $id) {
		$clsDesc = DescFactory::Instance()->getDesc($className);
		if (empty($clsDesc->primaryKey) || (is_array($clsDesc->primaryKey) && count($clsDesc->primaryKey) != 1)) {
			throw new MethodCallErrorException('the calss ' . $className . ' does not has one primary key.');
		}
		
		$pk = is_array($clsDesc->primaryKey) ? $clsDesc->primaryKey[0] : $clsDesc->primaryKey;
		$cond = new Condition($pk . '=' . $id);
		
		$ret = $this->where($className, $cond, 0, 1, $clsDesc);
		if (is_array($ret) && count($ret) > 0) {
			return $ret[0];
		} else {
			return null;
		}
	}

	public function getRelatedAttribute (ClassAttribute $attr, DataClass $dataObj, ClassDesc $clsDesc) {
		$myProp = $attr->selfAttribute2Relationship;
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
			$val = $this->where($attr->belongClass, new Condition($attr->anotherAttribute2Relationship . '=' . $myVal), 
					null, 0, $amount);
		} else { // 有关系表记录
			$sqlMyVal = $this->mDatabaseClient->change2SqlValue($myVal, $clsDesc->attribute[$myProp]->var);
			$sql = "SELECT {$attr->anotherAttributeInRelationship} FROM {$attr->relationshipName} WHERE {$attr->selfAttributeInRelationship}=$sqlMyVal";
			$ret = $this->mDatabaseClient->select($sql);
			
			$idArr = array();
			foreach ($ret as $row) {
				$idArr[] = $row[$attr->anotherAttributeInRelationship];
			}
			$cond = new Condition();
			$cond->add($attr->anotherAttribute2Relationship, 'in', $idArr);
			
			$val = $this->where($attr->belongClass, $cond);
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

	public function where ($className, Condition $cond = null, $start = 0, $num = self::MAX_AMOUNT, ClassDesc $clsDesc = null) {
		if (null == $clsDesc) {
			$clsDesc = DescFactory::Instance()->getDesc($className);
		}
		
		$sqlWhere = self::CreateSqlWhere($clsDesc, $cond, $this->mDatabaseClient);
		$sql = $this->createSqlSelect($clsDesc);
		if (! empty($sqlWhere)) {
			$sql .= 'WHERE ' . $sqlWhere;
		}
		$ret = $this->mDatabaseClient->select2Object($sql, $className, $start, $num);
		
		return $ret;
	}

	protected function createSqlSelect (ClassDesc $clsDesc) {
		$sql = 'SELECT ';
		foreach ($clsDesc->attribute as $attr) {
			if ('class' == $attr->var) {
				continue;
			}
			
			$name = $attr->persistentName;
			if (empty($name)) {
				continue;
			}
			
			$sql .= $name . ',';
		}
		$sql[strlen($sql) - 1] = ' ';
		
		$sql .= 'FROM ' . $clsDesc->persistentName . ' ';
		
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
			
			$key = $attr->persistentName;
			$val = $db->change2SqlValue($item->value, $attr->var);
			
			if (Condition::OPERATION_IN == $item->operation) {
				$condSqlArr[] = "$key {$item->operation} (" . implode(',', $val) . ')';
			} else {
				$condSqlArr[] = $key . $item->operation . $val;
			}
		}
		
		foreach ($condition->children as $child) {
			$condSqlArr[] = $this->createSqlWhere($child, $clsDesc);
		}
		
		$connector = Condition::RELATIONSHIP_OR == $condition->relationship ? ' OR ' : ' AND ';
		$sql = implode($connector, $condSqlArr);
		if (count($condSqlArr) > 1) {
			$sql = "($sql)";
		}
		
		return $sql;
	}
}
?>