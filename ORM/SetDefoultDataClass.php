<?php

namespace Framework\ORM;

use Framework\ORM\DataClass;
use HFC\Exception\ParameterErrorException;

/**
 * 数据填充默认数据类。
 */
class SetDefoultDataClass extends DataClass {
	
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

	protected function setCreatedTime ($t) {
		if (! property_exists($this, 'createdTime')) {
			return;
		}
		
		if (null == $t) {
			$this->createdTime = new \DateTime();
		} else {
			if (is_string($t)) {
				if ('0000-00-00 00:00:00' == $t) {
					$this->createdTime = new \DateTime();
				} else {
					try {
						$this->createdTime = \DateTime::createFromFormat('Y-m-d H:i:s', $t);
					} catch (\Exception $e) {
						throw new ParameterErrorException('DateTime format error.');
					}
				}
			} else if ($t instanceof \DateTime) {
				$this->createdTime = $t;
			} else {
				throw new ParameterErrorException('DateTime format error.');
			}
		}
	}
}

?>