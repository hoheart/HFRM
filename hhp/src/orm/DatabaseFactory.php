<?php

namespace orm;

use hfc\database\DatabaseClient;
use hfc\exception\ParameterErrorException;

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

	public function getDataMapList ($className, Condition $condition = null, ClassDesc $clsDesc = null) {
		if (null == $clsDesc) {
			$descFactory = DescFactory::Instance();
			$clsDesc = $descFactory->getDesc($className);
			
			if (null == $clsDesc) {
				throw new ParameterErrorException('Can not found desc for class: ' . $className);
			}
		}
		
		$sql = $this->createSqlSelect($clsDesc);
		$sqlWhere = self::CreateSqlWhere($clsDesc, $condition);
		if (! empty($sqlWhere)) {
			$sql .= 'WHERE ' . $sqlWhere;
		}
		
		$dbret = $this->mDatabaseClient->query($sql)->execute();
		
		// 取得结果value
		$arr = array();
		$row = $dbret->next();
		while ($row) {
			$arr[] = $row;
			$row = $dbret->next();
		}
		
		return $arr;
	}

	protected function createSqlSelect (ClassDesc $clsDesc) {
		$sql = 'SELECT ';
		foreach ($clsDesc->attribute as $attr) {
			if (ClassAttribute::DATA_TYPE_CLASS == $attr->dataType) {
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
	static public function CreateSqlWhere (ClassDesc $clsDesc, Condition $condition = null, 
			DatabaseClient $db) {
		if (null == $condition) {
			return '';
		}
		
		$condSqlArr = array();
		foreach ($condition->itemList as $item) {
			$attr = $clsDesc->attribute[$item->key];
			
			$key = $attr->persistentName;
			$val = $db->change2SqlValue($item->value, $attr->dataType);
			
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