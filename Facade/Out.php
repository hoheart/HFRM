<?php

namespace Framework\Facade;

use Framework\App;

class Out {

	static public function out ($str) {
		App::Instance()->getOutputStream()->write($str);
	}

	static public function header ($val, $replace = true, $code = null) {
		$s = App::Instance()->getOutputStream();
		if (null !== $code) {
			$s->status($code);
		}
		
		$pos = strpos($val, ':');
		$key = substr($val, 0, $pos);
		$val = substr($val, $pos + 1);
		$s->header($key, ltrim($val));
	}
}