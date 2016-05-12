<?php

namespace Framework\ORM;

use Framework\ORM\DataClass;

/**
 * 数据填充默认数据类。
 * 
 * @deprecated 这是特定项目的遗留类
 */
class SetDefaultDataClass extends DataClass {
	
	/**
	 *
	 * @var integer 客户端类型
	 */
	protected $terminalType;
	
	/**
	 *
	 * @var string guid
	 */
	protected $guid;

	public function __construct ($t = null) {
		parent::__construct();
		
		// 设置客户端类型
		$this->setTerminalType();
		// 设置guid
		$this->setGuid();
	}

	public function setTerminalType () {
		$this->terminalType = 1;
		
		return $this;
	}

	public function setGuid ($guid = '') {
		if ($guid != '') {
			$this->guid = $guid;
		} else {
			$this->guid = uuid_create();
		}
		
		return $this;
	}
}