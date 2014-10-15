<?php

namespace hhp\exception;

use hhp\exception\SystemErrcode;

class APINotAvailableException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::APINotAvailable;
		$this->message = $msg;
	}
}
?>