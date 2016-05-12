<?php

namespace hfc\exception;

class SystemAPIErrorException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::SystemAPIError;
		$this->message = $msg;
	}
}
?>