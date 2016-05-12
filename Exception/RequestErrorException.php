<?php

namespace hhp\exception;

use hhp\exception\UserErrcode;

class RequestErrorException extends \Exception {
	public function __construct($msg) {
		$this->code = UserErrcode::RequestError;
		$this->message = $msg;
	}
}
?>