<?php

namespace Hhp\Exception;

use Hhp\Exception\SystemErrcode;

class ClassNotFoundException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::ClassNotFound;
		$this->message = $msg;
	}
}
?>