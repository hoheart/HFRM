<?php

namespace Hhp\Exception;

use Hhp\Exception\SystemErrcode;

class NotImplementedException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::NotImplemented;
		$this->message = $msg;
	}
}
?>