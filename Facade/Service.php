<?php

namespace Framework\Facade;

use Framework\App;

class Service {

	public static function get ($name, $caller = null) {
		if (null == $caller) {
			list ($caller, $callerModuleName) = App::GetCallerModule();
		}
		
		return App::Instance()->getService($name, $caller);
	}
}