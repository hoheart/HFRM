<?php

namespace Framework\Exception;

class ModuleNotAvailableException extends \Exception {

	public function __construct ($msg = '') {
		$this->code = SystemErrcode::ModuleNotEnable;
		$this->message = $msg;
	}
}
?>