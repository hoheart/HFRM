<?php

namespace ORM;

/**
 * 从数据库中取出各种数据类的工厂类。
 *
 * @author Hoheart
 *        
 */
class DatabaseFactory extends AbstractDataFactory {
	protected $mDb = null;

	public function __construct () {
	}

	static public function Instance () {
		static $me = null;
		if (null === $me) {
			$me = new DatabaseFactory();
		}
		
		return $me;
	}

	public function setDBClient ($client) {
		$this->mDb = $client;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \icms\Evaluation\AbstractDataFactory::getDataMapList()
	 */
	public function getDataMapList ($className, Condition $condition = null, ClassDesc $clsDesc = null) {
		if (null == $clsDesc) {
			$descFactory = DescFactory::Instance();
			$clsDesc = $descFactory->getDesc($className);
			
			if (null == $clsDesc) {
				throw new \Exception('Can not found desc for class: ' . $className);
			}
		}
		
		$sql = $this->createSqlSelect($clsDesc);
		$sqlWhere = $this->createSqlWhere($clsDesc, $condition);
		if (! empty($sqlWhere)) {
			$sql .= 'WHERE ' . $sqlWhere;
		}
		
		$dbret = $this->mDb->query($sql)->execute();
		
		// 取得结果value
		$arr = array();
		while ($row = $dbret->next()) {
			$arr[] = $row;
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
	protected function createSqlWhere (ClassDesc $clsDesc, Condition $condition = null) {
		if (null == $condition) {
			return '';
		}
		
		$condSqlArr = array();
		foreach ($condition->itemList as $item) {
			$attr = $clsDesc->attribute[$item->key];
			
			$key = $attr->persistentName;
			$val = $this->changeValue2Sql($item->value, $attr->dataType);
			
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

	protected function changeValue2Sql ($val, $dataType) {
		$v = null;
		switch ($type) {
			case ClassAttribute::DATA_TYPE_STRING:
			case ClassAttribute::DATA_TYPE_FILE:
			case ClassAttribute::DATA_TYPE_DATE:
			case ClassAttribute::DATA_TYPE_TIME:
			case ClassAttribute::DATA_TYPE_DATE_TIME:
				$v = "'$val'";
				break;
			case ClassAttribute::DATA_TYPE_NUMERICAL:
			case ClassAttribute::DATA_TYPE_SMALL_INTEGER:
			case ClassAttribute::DATA_TYPE_INTEGER:
			case ClassAttribute::DATA_TYPE_LONG_INTEGER:
				$v = (int) $val;
				break;
			case ClassAttribute::DATA_TYPE_FLOAT:
				$v = (float) $val;
				break;
			case ClassAttribute::DATA_TYPE_CLASS:
				$v = null;
				break;
			case ClassAttribute::DATA_TYPE_BOOLEAN:
				$v = $val ? 1 : 0;
				break;
			default:
				$v = $val;
				break;
		}
		
		return $v;
	}
}
?>