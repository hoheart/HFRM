<?php

namespace Framework;

class StandardOutputStream implements IOutputStream {

	public function status ($code) {
		http_response_code($code);
	}

	public function header ($key, $val) {
		header($key . ': ' . $val);
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