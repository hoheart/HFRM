<?php

namespace orm;

use hfc\database\DatabaseClient;
use hfc\exception\ParameterErrorException;
use hhp\exception\ConfigErrorException;
use hfc\exception\MethodCallErrorException;

/**
 * 从数据库中取出各种数据类的工厂类。
 *
 * @author Hoheart
 *        
 */
class DatabaseFactory extends AbstractDataFactory {
	
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
		
		$ret = $this->where($className, $cond, $clsDesc);
		
		return $ret[0];
	}

	public function where ($className, Condition $cond = null, ClassDesc $clsDesc = null) {
		if (null == $clsDesc) {
			$clsDesc = DescFactory::Instance()->getDesc($className);
		}
		
		$sqlWhere = self::CreateSqlWhere($clsDesc, $cond, $this->mDatabaseClient);
		$sql = $this->createSqlSelect($clsDesc);
		if (! empty($sqlWhere)) {
			$sql .= 'WHERE ' . $sqlWhere;
		}
		$ret = $this->mDatabaseClient->select($sql);
		
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
			
			$condSqlArr[] = $key . $item->operation . $val;
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