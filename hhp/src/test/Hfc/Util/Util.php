<?php

namespace test\Hfc\Util;

use Hfc\Util\Util as HUtil;

class Util {

	public function test () {
		if (! $this->mergeArray()) {
			return false;
		}
		
		return true;
	}

	public function mergeArray () {
		$a = array(
			1,
			2
		);
		
		$ret = HUtil::mergeArray($a, null);
		if ($a != $ret) {
			return false;
		}
		
		$ret = HUtil::mergeArray(null, $a);
		if ($a != $ret) {
			return false;
		}
		
		$b = array(
			2,
			'c' => 11
		);
		$c = array(
			2,
			2,
			'c' => 11
		);
		if ($c != HUtil::mergeArray($a, $b)) {
			return false;
		}
		
		return true;
	}
}
?>