<?php

namespace test;

use Hhp\IExecuter;

class Executer implements IExecuter {

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new Executer();
		}
		
		return $me;
	}

	public function run ($do = null) {
		echo 1;
		
		$do = new \stdClass();
		$do->abc = 'abc';
	}
}

class TestPreExecuter2 implements IExecuter {

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new TestPreExecuter2();
		}
		
		return $me;
	}

	public function run ($do = null) {
		echo 2;
		
		if (! $do instanceof \stdClass) {
			return false;
		}
		if ('abc' != $do->abc) {
			return false;
		}
		
		$do->edf = 'edf';
		
		return $do;
	}
}

class TestLaterExecuter1 implements IExecuter {

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new TestLaterExecuter1();
		}
		
		return $me;
	}

	public function run ($do = null) {
		echo 3;
		
		if (! $do instanceof View) {
			return false;
		}
		
		$do = new \stdClass();
		$do->later = 'later';
		
		return $do;
	}
}

class TestLaterExecuter2 implements IExecuter {

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new TestLaterExecuter2();
		}
		
		return $me;
	}

	public function run ($do = null) {
		echo 4;
		
		if (! $do instanceof \stdClass) {
			return false;
		}
		
		if ('later' != $do->later) {
			return false;
		}
		
		return true;
	}
}

?>