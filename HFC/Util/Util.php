<?php

namespace HFC\Util;

class Util {

	static public function dump ($param = 'no param', $mode = 1, $is_exit = true) {
		header('Content-type:text/html;charset=utf-8');
		list ($open, $close) = array(
			'<pre>' . PHP_EOL,
			'</pre>'
		);
		$method = array(
			'var_dump',
			'print_r',
			'var_export'
		);
		echo $open;
		$method[$mode]($param);
		$is_exit ? exit($close) : print($close);
	}
}