<?php

namespace Framework\Facade;

use Framework\Router\Redirector;

class Redirect {

	public static function __callStatic ($method, $args) {
		return call_user_func_array(array(
			Redirector::Instance(),
			$method
		), $args);
	}
}