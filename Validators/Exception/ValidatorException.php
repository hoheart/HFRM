<?php

namespace Framework\Validators\Exception;

class ValidatorException extends \Exception {

	public function __construct($message = "", $code = ValidatorErrorCode::ValidatorError) {
		$this->code = $code;
		$this->message = $message;
	}
}