<?php

namespace Hfc\Database;

use Hfc\Exception\SystemErrcode;

class DatabaseQueryException extends \Exception {

	public function __construct ($msg) {
		$this->code = SystemErrcode::DatabaseQuery;
		$this->message = $msg;
	}
}
?>