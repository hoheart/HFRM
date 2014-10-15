<?php

namespace hhp\exception;

use hhp\exception\SystemErrcode;

class ModuleNotEnableException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::ModuleNotEnable;
		$this->message = $msg;
	}
}
?>