<?php

namespace Framework\Swoole;

use Framework\Exception\NotImplementedException;
use Framework\IHttpResponse;

class HttpResponse implements IHttpResponse {
	
	/**
	 *
	 * @var \swoole_http_response
	 */
	protected $mSwooleResponse = null;
	
	/**
	 *
	 * @var string $mBody
	 */
	protected $mBody = '';

	public function __construct ($resp) {
		$this->mSwooleResponse = $resp;
	}

	public function setStatusCode ($code) {
		$this->mSwooleResponse->status($code);
	}

	public function setReasonPhrase ($reason) {
		throw new NotImplementedException();
	}

	public function setHeader ($fieldName, $value, $replace = true) {
		$this->mSwooleResponse->header($fieldName, $value);
	}

	public function setCookie ($key, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false) {
		$this->mSwooleResponse->cookie($key, $value, $expire, $path, $domain, $secure, $httponly);
	}

	/**
	 * 设置响应内容
	 *
	 * @param string $body        	
	 */
	public function setBody ($body) {
		$this->mBody = $body;
	}

	public function getBody () {
		return $this->mBody;
	}
}