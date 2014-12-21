<?php

namespace hfc\exception;

class MethodCallErrorException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::MethodCallError;
		$this->message = $msg;
	}
}
?>