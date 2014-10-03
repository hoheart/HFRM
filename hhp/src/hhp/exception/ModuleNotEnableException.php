<?php

namespace Hhp\Exception;

use Hhp\Exception\SystemErrcode;

class ModuleNotEnableException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::ModuleNotEnable;
		$this->message = $msg;
	}
}
?>