<?php

namespace orm;

class ORMService {
	
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

	public function save (DataClass $dataObj) {
		return $this->mPersistence->save($dataObj, null);
	}

	public function delete ($className, Condition $condition = null) {
		return $this->mPersistence->delete($className, $condition);
	}

	public function get () {
	}

	public function getDataMapList ($className, Condition $condition = null, ClassDesc $clsDesc = null) {
		return $this->mFactory->getDataMapList($className, $condition, $clsDesc);
	}

	public function getDataMapListFromRelation (ClassDesc $clsDesc = null, $attrName, $val) {
		return $this->mFactory->getDataMapListFromRelation($clsDesc, $attrName, $val);
	}
}