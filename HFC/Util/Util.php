<?php

namespace Framework\HFC\Util;

use Framework\Facade\Out;

class Util {

	static public function dump ($param = 'no param', $mode = 1, $is_exit = true) {
		Out::header('Content-type:text/html;charset=utf-8');
		list ($open, $close) = array(
			'<pre>' . PHP_EOL,
			'</pre>'
		);
		$method = array(
			'var_dump',
			'print_r',
			'var_export'
		);
		Out::out($open);
		$method[$mode]($param);
		Out::out($close);
		if ($is_exit) {
			exit();
		}
	}
}