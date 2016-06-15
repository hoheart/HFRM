<?php

namespace Framework\Facade;

class Config {

	public static function __callStatic ($method, $args) {
		$objConf = \Framework\Config::Instance();
		return call_user_func_array(array(
			$objConf,
			$method
		), $args);
	}
}