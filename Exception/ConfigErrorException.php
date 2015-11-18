<?php

namespace Framework\Exception;

class ConfigErrorException extends \Exception {

	public function __construct ($msg = '') {
		$this->code = SystemErrcode::ConfigError;
		$this->message = $msg;
	}
}
?>