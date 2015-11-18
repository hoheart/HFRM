<?php

namespace Framework\Exception;

class AmbiguousCallException extends \Exception {

	public function __construct ($msg = '') {
		$this->code = SystemErrcode::AmbiguousCall;
		$this->message = $msg;
	}
}
?>