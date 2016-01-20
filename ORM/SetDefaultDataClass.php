<?php

namespace Framework\ORM;

use Framework\ORM\DataClass;

/**
 * 数据填充默认数据类。
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
		// 设置创建时间
		$this->setCreatedTime($t);
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