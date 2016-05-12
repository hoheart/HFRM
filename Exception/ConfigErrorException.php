<?php

namespace hhp\exception;

use hhp\exception\SystemErrcode;

class ConfigErrorException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::ConfigError;
		$this->message = $msg;
	}
}
?>