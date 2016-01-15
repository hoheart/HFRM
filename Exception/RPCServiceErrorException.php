<?php

namespace Framework\Exception;

class RPCServiceErrorException extends \Exception {

	public function __construct ($msg = '') {
		$this->code = SystemErrcode::RPCServiceError;
		$this->message = $msg;
	}
}