<?php

namespace test\ServiceManagerTest;

class TestServiceFactory {

	static public function create ($conf) {
		static $s = null;
		
		if (null == $s) {
			$s = new TestService($conf);
		}
		
		return $s;
	}
}

?>