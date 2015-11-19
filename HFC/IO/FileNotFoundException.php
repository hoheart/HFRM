<?php

namespace HFC\IO;

use HFC\Exception\SystemErrcode;

class FileNotFoundException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::FileNotFound;
		$this->message = $msg;
	}
}
?>