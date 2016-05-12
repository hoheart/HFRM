<?php

namespace test\HfcTest\UtilTest;

use Hfc\Util\Util as HUtil;
use test\AbstractTest;

class UtilTest extends AbstractTest {

	public function test () {
		$this->mergeArray();
	}

	public function mergeArray () {
		$a = array(
			1,
			2
		);
		
		$ret = HUtil::mergeArray($a, null);
		if ($a != $ret) {
			$this->throwError('', __METHOD__, __LINE__);
		}
		
		$ret = HUtil::mergeArray(null, $a);
		if ($a != $ret) {
			$this->throwError('', __METHOD__, __LINE__);
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
			$this->throwError('', __METHOD__, __LINE__);
		}
	}
}
?>