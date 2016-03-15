<?php

namespace Framework\Output;

class StandardOutputStream implements IOutputStream {

	public function status ($code) {
		http_response_code($code);
	}

	public function header ($val, $replace = true, $code = 200) {
		header($val);
	}

	public function write ($str, $offset = 0, $count = -1) {
		$subStr = substr($str, $offset, $count);
		echo $subStr;
	}

	public function flush () {
		ob_flush();
		flush();
	}

	public function close () {
	}
}