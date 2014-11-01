<?php

namespace hfc\database;

use hfc\exception\SystemErrcode;

class DatabaseConnectException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::DatabaseConnect;
		$this->message = $msg;
	}
}
?>