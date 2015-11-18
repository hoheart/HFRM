<?php

namespace Framework\Facade;

use Framework\Router\URLGenerator;

class URL {

	public static function __callStatic ($method, $args) {
		static $url = null;
		if (null == $url) {
			$url = new URLGenerator();
		}
		
		return call_user_func_array(array(
			$url,
			$method
		), $args);
	}
}