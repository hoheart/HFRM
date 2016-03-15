<?php

namespace Framework\Output;

use Framework\Facade\Config;

class SwooleHttpOutputStream implements IOutputStream {
	
	/**
	 *
	 * @var swoole_http_response
	 */
	protected $mResponse = null;

	public function __construct () {
	}

	public function status ($code) {
		$this->mResponse->status($code);
	}

	public function header ($key, $val) {
		$this->mResponse->header($key, $val);
	}

	public function cookie ($key, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false) {
		$this->mResponse->cookie($key, $value, $expire, $path, $domain, $secure, $httponly);
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
		return $this;
	}

	public function close () {
		$sessionCookieName = Config::get('session.cookieName');
		$sessionId = session_id();
		if (! empty($sessionId) && $sessionId !== $_COOKIE[$sessionCookieName]) {
			$this->cookie($sessionCookieName, $sessionId);
		}
		$this->mResponse->end();
		return $this;
	}
}