<?php

namespace orm;

class ORMService {
	protected $mFactory = null;
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

	public function save ($dataObj, $isSaveSub = false, ClassDesc $clsDesc = null) {
		return $this->mPersistence->save($dataObj, $isSaveSub, $clsDesc);
	}

	public function add ($dataObj, $isSaveSub = false, ClassDesc $clsDesc = null) {
		return $this->mPersistence->add($dataObj, $isSaveSub, $clsDesc);
	}

	public function update ($dataObj, $isSaveSub = false, ClassDesc $clsDesc = null) {
		return $this->mPersistence->update($dataObj, $isSaveSub, $clsDesc);
	}

	public function delete ($className, Condition $condition = null) {
		return $this->mPersistence->delete($className, $condition);
	}

	public function getDataMapList ($className, Condition $condition = null, ClassDesc $clsDesc = null) {
		return $this->mFactory->getDataMapList($className, $condition, $clsDesc);
	}

	public function getDataMapListFromRelation (ClassDesc $clsDesc = null, $attrName, $val) {
		return $this->mFactory->getDataMapListFromRelation($clsDesc, $attrName, $val);
	}

	public function change2SqlValue ($dataObj, $attrArr, $isSaveSub, $isAdd = true) {
		return $this->mPersistence->change2SqlValue($dataObj, $attrArr, $isSaveSub, $isAdd);
	}
}