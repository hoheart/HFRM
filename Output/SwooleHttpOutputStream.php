<?php

namespace Framework\Output;

use Framework\Response\IResponse;

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

	public function output (IResponse $resp) {
		$this->mResponse->status($resp->getStatus());
		
		foreach ($resp->getAllHeader() as $key => $val) {
			if (is_array($val)) {
				foreach ($val as $one) {
					$this->mResponse->header($key, $one);
				}
			} else {
				$this->mResponse->header($key, $val);
			}
		}
		
		foreach ($resp->getAllCookie() as $key => $cookie) {
			$this->mResponse->cookie($key, $cookie['value'], $cookie['expire'], $cookie['path'], $cookie['domain'], 
					$cookie['secure'], $cookie['httponly']);
		}
		
		$body = $resp->getBody();
		if ('' !== $body) {
			$this->mResponse->write($resp->getBody());
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