<?php

namespace Framework\Response;

use Framework\Output\IOutputStream;

class HttpResponse implements IHttpResponse {
	
	/**
	 * 最大缓存，超过这个值，就输出
	 *
	 * @var int
	 */
	const MAX_CACHE_SIZE = 2097152; // 2M
	
	/**
	 * Http状态码
	 *
	 * @var int $status
	 */
	protected $mStatus = 200;
	
	/**
	 * 头信息
	 *
	 * @var array $headerMap
	 */
	protected $mHeaderMap = array();
	
	/**
	 *
	 * @var array $mCookieMap
	 */
	protected $mCookieMap = array();
	
	/**
	 *
	 * @var string $mBody
	 */
	protected $mBody = '';
	
	/**
	 *
	 * @var IOutputStream $mOutputStream
	 */
	protected $mOutputStream = null;

	public function status ($code) {
		$this->mStatus = $code;
	}

	public function getStatus () {
		return $this->mStatus;
	}

	public function header ($key, $val, $replace = true) {
		if (! $replace && array_key_exists($key, $this->mHeaderMap)) {
			$oldVal = $this->mHeaderMap[$key];
			if (is_array($oldVal)) {
				$this->mHeaderMap[$key][] = $val;
			} else {
				$this->mHeaderMap[$key] = array(
					$oldVal,
					$val
				);
			}
		} else {
			$this->mHeaderMap[$key] = $val;
		}
	}

	public function getHeader ($key) {
		return $this->mHeaderMap[$key];
	}

	public function getAllHeader () {
		return $this->mHeaderMap;
	}

	public function cookie ($key, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false) {
		$cookie = array(
			'value' => $value,
			'expire' => $expire,
			'path' => $path,
			'domain' => $domain,
			'secure' => $secure,
			'httponly' => $httponly
		);
		
		$this->mCookieMap[$key] = $value;
	}

	public function getAllCookie () {
		return $this->mCookieMap;
	}

	public function getCookie ($key) {
		return $this->mCookieMap[$key];
	}

	public function addBody ($content) {
		$this->mBody .= $content;
	}

	public function setBody ($body) {
		$this->mBody = $body;
	}

	public function getBody () {
		return $this->mBody;
	}

	public function setOutputStream (IOutputStream $stream) {
		$this->mOutputStream = $stream;
	}

	public function addContent ($content) {
		$this->addBody($content);
	}

	public function getContent () {
		return $this->mBody;
	}

	public function clear () {
		$this->mStatus = 0;
		$this->mBody = '';
		$this->mCookieMap = array();
		$this->mHeaderMap = array();
	}
}