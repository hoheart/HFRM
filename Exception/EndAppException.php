<?php

namespace Framework\Exception;

use Framework;

class EndAppException extends \Exception {

	public function __construct ($msg = '') {
		$this->code = SystemErrcode::EndService;
		$this->message = $msg;
	}
}