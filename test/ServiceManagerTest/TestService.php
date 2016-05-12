<?php

namespace test\ServiceManagerTest;

/**
 * 用于测试ServiceManager的测试类
 *
 * @author Hoheart
 *        
 */
class TestService {
	protected $mConf = null;

	public function __construct ($conf) {
		$this->mConf = $conf;
	}

	public function getParam () {
		return $this->mConf['param'];
	}
}

?>