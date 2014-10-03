<?php

namespace Hhp\Exception;

use Hhp\Exception\SystemErrcode;

class ConfigErrorException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::ConfigError;
		$this->message = $msg;
	}
}
?>