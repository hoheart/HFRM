<?php

namespace Framework\HFC\Exception;

class SQLInjectionRiskException extends \Exception {

	public function __construct ($msg = '') {
		$this->code = SystemErrcode::SQLInjectionRisk;
		$this->message = $msg;
	}
}