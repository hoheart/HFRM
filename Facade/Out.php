<?php

namespace Framework\Facade;

use Framework\App;

class Out {

	static public function out ($str) {
		App::Instance()->getOutputStream()->write($str);
	}
}