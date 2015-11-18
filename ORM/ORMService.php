<?php

namespace Framework\ORM;

use Framework\App;
use HFC\Database\DatabaseTransaction;
use Framework\IService;

class ORMService implements IService {
	
	/**
	 * 数据获取类
	 *
	 * @var AbstractDataFactory
	 */
	protected $mFactory = null;
	
	/**
	 * 数据保存类
	 *
	 * @var AbstractPersistence
	 */
	protected $mPersistence = null;

	public function __construct ($conf) {
		$fConf = $conf['factory'];
		$fcls = $fConf['class'];
		$fobj = new $fcls();
		$this->mFactory = $fobj->create();
		
		$fConf = $conf['persistence'];
		$fcls = $fConf['class'];
		$fobj = new $fcls();
		$this->mPersistence = $fobj->create();
	}

	public function init (array $conf) {
	}

	public function start () {
	}

	public function stop () {
	}

	public function save (DataClass $dataObj) {
		list ($caller, $callerModuleName) = App::GetCallerModule();
		$dbClient = App::Instance()->getService('db', $caller);
		$this->mPersistence->setDatabaseClient($dbClient);
		
		return $this->mPersistence->save($dataObj, null);
	}

	public function delete ($className, Condition $condition = null) {
		list ($caller, $callerModuleName) = App::GetCallerModule();
		$dbClient = App::Instance()->getService('db', $caller);
		$this->mPersistence->setDatabaseClient($dbClient);
		
		return $this->mPersistence->delete($className, $condition);
	}

	/**
	 * 开始一个事务。
	 * 开始事务之后，用\HFC\Database\DatabaseTransaction来提交和回滚事务。
	 *
	 * @return \HFC\Database\DatabaseTransaction
	 */
	public function beginTransaction () {
		$dbClient = $this->mFactory->getDatabaseClient();
		if (null == $dbClient) {
			$dbClient = $this->mPersistence->getDatabaseClient();
		}
		if (null == $dbClient) {
			list ($caller, $callerModuleName) = App::GetCallerModule();
			$dbClient = App::Instance()->getService('db', $caller);
		}
		
		return new DatabaseTransaction($dbClient);
	}

	public function get ($clsName, $id) {
		list ($caller, $callerModuleName) = App::GetCallerModule();
		$dbClient = App::Instance()->getService('db', $caller);
		$this->mFactory->setDatabaseClient($dbClient);
		
		return $this->mFactory->get($clsName, $id);
	}

	public function count ($clsName, Condition $cond = null) {
		list ($caller, $callerModuleName) = App::GetCallerModule();
		$dbClient = App::Instance()->getService('db', $caller);
		$this->mFactory->setDatabaseClient($dbClient);
		
		return $this->mFactory->count($clsName, $cond);
	}

	public function where ($clsName, Condition $cond = null, $start = 0, $num = DatabaseFactory::MAX_AMOUNT, $order = '') {
		list ($caller, $callerModuleName) = App::GetCallerModule();
		$dbClient = App::Instance()->getService('db', $caller);
		$this->mFactory->setDatabaseClient($dbClient);
		
		return $this->mFactory->where($clsName, $cond, $start, $num, null, $order);
	}
}