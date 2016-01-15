<?php

namespace Framework\ORM;

use HFC\Database\DatabaseClient;

/**
 * 抽象的数据工厂
 *
 * @author Hoheart
 *        
 */
abstract class AbstractDataFactory {
	
	/**
	 *
	 * @var DatabaseClient
	 */
	protected $mDatabaseClient = null;

	/**
	 * 根据类的唯一主键的值进行查询
	 *
	 * @param integer $id        	
	 * @return DataClass 返回一个对象
	 */
	abstract public function get ($className, $id);

	/**
	 * 根据Condition 查询一个对象
	 * @param $className
	 * @param Condition $cond
	 * @return mixed
	 */
	abstract public function getOne($className, Condition $cond);

	abstract public function where ($className, Condition $cond = null);

	public function setDatabaseClient (DatabaseClient $client) {
		$this->mDatabaseClient = $client;
	}

	public function getDatabaseClient () {
		return $this->mDatabaseClient;
	}
}