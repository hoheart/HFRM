<?php

namespace Hhp\Exception;

use Hhp\Exception\UserErrcode;

class RequestErrorException extends \Exception {

	public function __construct ($msg) {
		$this->code = UserErrcode::RequestError;
		$this->message = $msg;
	}
}
?>