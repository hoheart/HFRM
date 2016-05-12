<?php

namespace orm\exception;

class NoPropertyException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::NoPropertyError;
		$this->message = $msg;
	}
}
?>