<?php

namespace hfc\database;

use hfc\exception\SystemErrcode;

class DatabaseQueryException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::DatabaseQuery;
		$this->message = $msg;
	}
}
?>