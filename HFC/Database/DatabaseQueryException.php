<?php

namespace HFC\Database;

use HFC\Exception\SystemErrcode;

class DatabaseQueryException extends \Exception {
	
	/**
	 * 源错误码，即数据库错误码
	 *
	 * @var string
	 */
	protected $mSourceCode = 0;
	
	/**
	 *
	 * @var \Exception
	 */
	protected $mSourceException = null;

	public function __construct ($msg = '', \Exception $previous = null) {
		parent::__construct($msg, SystemErrcode::DatabaseQuery, $previous);
	}

	public function setSourceCode ($code) {
		$this->mSourceCode = $code;
	}

	public function getSourceCode () {
		return $this->mSourceCode;
	}

	public function setSourceException (\Exception $e) {
		$this->mSourceException = $e;
	}

	public function getSourceException () {
		return $this->mSourceException;
	}
}