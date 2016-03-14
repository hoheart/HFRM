<?php

namespace Framework\Swoole;

use Framework\IOutputStream;

class HttpOutputStream implements IOutputStream {
	
	/**
	 *
	 * @var swoole_http_response
	 */
	protected $mResponse = null;

	public function __construct () {
	}

	public function header () {
	}

	public function setSwooleResponse ($resp) {
		$this->mResponse = $resp;
	}

	public function write ($str, $offset = 0, $count = null) {
		$content = null === $count ? substr($str, $offset) : substr($str, $offset);
		if (strlen($content) > 0) {
			$this->mResponse->write($content);
		}
	}

	public function flush () {
	}

	public function close () {
		$this->mResponse->end();
	}
}