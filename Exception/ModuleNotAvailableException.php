<?php

namespace hhp\exception;

use hhp\exception\SystemErrcode;

class ModuleNotAvailableException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::ModuleNotEnable;
		$this->message = $msg;
	}
}
?>