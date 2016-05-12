<?php

namespace Framework\Facade;

class Session {

	public static function __callStatic ($method, $args) {
		static $o = null;
		if (null == $o) {
			$o = \Framework\Session::Instance();
		}
		
		return call_user_func_array(array(
			$o,
			$method
		), $args);
	}
}