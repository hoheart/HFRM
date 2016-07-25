<?php

namespace Framework\Exception;

use Framework\HFC\Exception\SystemErrcode;

class NetworkErrorException extends \Exception {

	public function __construct ($msg = '') {
		$this->code = SystemErrcode::NetworkError;
		$this->message = $msg;
	}
}