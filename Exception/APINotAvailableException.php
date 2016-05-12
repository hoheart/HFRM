<?php

namespace Framework\Exception;

class APINotAvailableException extends \Exception {

	public function __construct ($msg = '') {
		$this->code = SystemErrcode::APINotAvailable;
		$this->message = $msg;
	}
}