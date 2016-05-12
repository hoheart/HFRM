<?php

namespace hfc\io;

use hfc\exception\SystemErrcode;

class FileNotFoundException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::FileNotFound;
		$this->message = $msg;
	}
}
?>