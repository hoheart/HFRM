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
		$sqlWhere = self::CreateSqlWhere($clsDesc, $condition, $this->mDatabaseClient);
		if (! empty($sqlWhere)) {
			$sql .= 'WHERE ' . $sqlWhere;
		}
		
		$dbret = $this->mDatabaseClient->select($sql);
		
		return $dbret;
	}

	/**
	 * 从关系中取得数据数组
	 *
	 * @param ClassAttribute $attr
	 *        	本类中对应属性的ClassAtrribute
	 *        	比如Group类中有个userArr的属性，要取得userArr对应的dataMap，就传userArr的ClassAttribute。
	 * @param object $val
	 *        	本类中该属性的值
	 */
	public function getDataMapListFromRelation (ClassDesc $clsDesc = null, $attrName, $val) {
		$attr = $clsDesc->attribute[$attrName];
		$relationshipName = $attr->relationshipName;
		$anotherAttrInRelationship = $attr->anotherAttributeInRelationship;
		$myAttrInRelationship = $attr->selfAttributeInRelationship;
		$selfAttr2Relationship = $clsDesc->attribute[$attr->selfAttribute2Relationship];
		$sqlVal = $this->mDatabaseClient->change2SqlValue($val, $selfAttr2Relationship->var);
		$amountType = $attr->amountType;
		$sql = "SELECT $anotherAttrInRelationship FROM $relationshipName WHERE $myAttrInRelationship = $sqlVal";
		
		$ret = array();
		switch ($amountType) {
			case 'little':
				$ret = $this->mDatabaseClient->select($sql, 0, 100);
				
				break;
			case 'large':
				$ret = $this->mDatabaseClient->select($sql);
				break;
			case 'single':
			default:
				$ret[] = $this->mDatabaseClient->getOne($sql);
				break;
		}
		
		$anotherAttr2Relationship = $attr->anotherAttribute2Relationship;
		$cond = new Condition();
		$cond->setRelationship(Condition::RELATIONSHIP_OR);
		foreach ($ret as $row) {
			$cond->add($anotherAttr2Relationship, '=', $row[$anotherAttrInRelationship]);
		}
		
		return $this->getDataMapList($attr->belongClass, $cond);
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
	static public function CreateSqlWhere (ClassDesc $clsDesc, Condition $condition = null, 
			DatabaseClient $db) {
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