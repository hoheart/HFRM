<?php

namespace Framework\HFC\IO;

use Framework\HFC\Exception\SystemErrcode;

class FileNotFoundException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::FileNotFound;
		$this->message = $msg;
	}
}