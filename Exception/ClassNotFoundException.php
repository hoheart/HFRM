<?php

namespace Framework\Exception;

class ClassNotFoundException extends \Exception {

	public function __construct ($msg = '') {
		$this->code = SystemErrcode::ClassNotFound;
		$this->message = $msg;
	}
}