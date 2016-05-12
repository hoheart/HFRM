<?php

namespace TestSub\controller;

use hhp\Controller;

class TestSubDisabledController extends Controller {

	public function testSub () {
		echo __CLASS__ . '::' . __METHOD__;
	}
}

?>