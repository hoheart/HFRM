<?php

namespace test\AppTest;

use hhp\IExecutor;

class Executor implements IExecutor {

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new Executor();
		}
		
		return $me;
	}

	public function run ($do = null) {
		$do = new \stdClass();
		$do->abc = '123';
		
		return $do;
	}
}

class TestPreExecutor2 implements IExecutor {

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new TestPreExecutor2();
		}
		
		return $me;
	}

	public function run ($do = null) {
		if (! $do instanceof \stdClass) {
			return false;
		}
		if ('123' != $do->abc) {
			return false;
		}
		
		$do->abc = '456';
		
		return $do;
	}
}

class TestLaterExecutor1 implements IExecutor {

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new TestLaterExecutor1();
		}
		
		return $me;
	}

	public function run ($do = null) {
		if (! $do instanceof \stdClass) {
			return false;
		}
		
		$do->abc = 'later1';
		
		return $do;
	}
}

class TestLaterExecutor2 implements IExecutor {

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new TestLaterExecutor2();
		}
		
		return $me;
	}

	public function run ($do = null) {
		if (! $do instanceof \stdClass) {
			return false;
		}
		
		if ('later1' != $do->abc) {
			return false;
		}
		
		$do->abc = 'later2';
		
		echo $do->abc;
	}
}

?>