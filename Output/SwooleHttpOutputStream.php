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
		$content = null === $count ? substr($str, $offset) : substr($str, $offset);
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
		
		foreach ($resp->getAllCooike() as $key => $cookie) {
			$this->mResponse->cookie($key, $cookie['value'], $cookie['expire'], $cookie['path'], $cookie['domain'], 
					$cookie['secure'], $cookie['httponly']);
		}
		
		$this->mResponse->wirte($resp->getBody());
	}

	public function flush () {
		return $this;
	}

	public function close () {
		$this->flush();
		$this->mResponse->end();
		return $this;
	}
}