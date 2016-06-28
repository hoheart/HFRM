<?php

namespace Framework\Swoole;

use Framework\Output\IOutputStream;

class SwooleHttpOutputStream implements IOutputStream {
	
	/**
	 *
	 * @var swoole_http_response
	 */
	protected $mResponse = null;

	public function __construct () {
	}

	public function setSwooleResponse ($resp) {
		$this->mResponse = $resp;
	}

	public function write ($str, $offset = 0, $count = null) {
		$content = $str;
		if (null == $count) {
			if (0 != $offset) {
				$content = substr($str, $offset);
			}
		} else {
			$content = substr($str, $offset, $count);
		}
		if (strlen($content) > 0) {
			$this->mResponse->write($content);
		}
	}

	public function flush () {
		return $this;
	}

	public function close () {
		$this->flush();
		@$this->mResponse->end();
		return $this;
	}
}