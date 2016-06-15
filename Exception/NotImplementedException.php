<?php

namespace Framework\Exception;

use Framework\HFC\Exception\SystemErrcode;

class NotImplementedException extends \Exception {

	public function __construct ($msg = '') {
		$this->code = SystemErrcode::NotImplemented;
		$this->message = $msg;
	}
}