<?php

namespace Framework\Exception;

use Framework\Exception\UserErrcode;

class NotFoundHttpException extends \Exception {

	public function __construct ($msg = '') {
		$this->code = UserErrcode::NotFoundHttp;
		$this->message = $msg;
	}
}