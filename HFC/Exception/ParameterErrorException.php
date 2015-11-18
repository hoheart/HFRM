<?php

namespace HFC\Exception;

class ParameterErrorException extends \Exception {

	public function __construct ($msg = '') {
		$this->code = UserErrcode::ParameterError;
		$this->message = $msg;
	}
}
?>