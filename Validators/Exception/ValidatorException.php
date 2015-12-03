<?php

namespace Framework\Validators\Exception;

class ValidatorException extends \Exception {

	public function __construct($message = "", $code = 100) {
		$this->code = $code;
		$this->message = $message;
	}
}

?>