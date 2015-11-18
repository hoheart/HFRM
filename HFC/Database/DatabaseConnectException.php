<?php

namespace HFC\Database;

use HFC\Exception\SystemErrcode;

class DatabaseConnectException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::DatabaseConnect;
		$this->message = $msg;
	}
}
?>