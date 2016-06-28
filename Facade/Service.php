<?php

namespace Framework\Facade;

use Framework\App;

class Service {

	public static function get ($name) {
		return App::Instance()->getService($name);
	}
}