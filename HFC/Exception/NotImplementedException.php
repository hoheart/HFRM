<?php

namespace Framework\HFC\Exception;

class NotImplementedException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::NotImplemented;
		$this->message = $msg;
	}
}