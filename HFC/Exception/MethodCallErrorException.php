<?php

namespace HFC\Exception;

class MethodCallErrorException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::MethodCallError;
		$this->message = $msg;
	}
}