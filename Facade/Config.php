<?php

namespace Framework\Facade;

use Framework\App;

class Config {

	public static function __callStatic ($method, $args) {
		if ('get' == $method) {
			list ($callerAlias, $callerName) = App::GetCallerModule();
			if ('framework' == $callerAlias) {
				$callerAlias = '';
			}
			
			$method = 'getModuleConfig';
			array_unshift($args, $callerAlias);
		}
		
		$objConf = \Framework\Config::Instance();
		return call_user_func_array(array(
			$objConf,
			$method
		), $args);
	}
}