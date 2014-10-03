<?php

namespace Hfc\Database;

use Hfc\Exception\SystemErrcode;

class DatabaseConnectException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::DatabaseConnect;
		$this->message = $msg;
	}
}
?>