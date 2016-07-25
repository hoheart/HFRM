<?php

namespace Framework\Http;

abstract class HttpMessage {
	/**
	 * 内容类型
	 *
	 * @var string
	 */
	const CONTENT_TYPE_URLENCODED = 'application/x-www-form-urlencoded';

	/**
	* header
	*/
	const HEADER_CONTENT_LENGTH = 'Content-Length';
	const HEADER_Transfer-Encoding
	
	/**
	 *
	 * @var string $mId
	 */
	protected $mId = '';
	
	/**
	 *
	 * @var string
	 */
	protected $mVersion = 'HTTP/1.1';
	
	/**
	 *
	 * @var map $mHeader
	 */
	protected $mHeader = array();
	
	/**
	 *
	 * @var map $mCookie
	 */
	protected $mCookieMap = array();
	
	/**
	 *
	 * @var map $mBody
	 */
	protected $mBodyMap = array();
	
	/**
	 *
	 * @var string $mBody
	 */
	protected $mBody = '';

	public function __construct () {
		$this->mId = uuid_create();
	}

	public function setId ($id) {
		$this->mId = $id;
	}

	public function getId () {
		return $this->mId;
	}

	public function setHeader ($fieldName, $value) {
		if ('Cookie' == $fieldName) {
			$this->mCookieMap[$fieldName] = $value;
		} else {
			$this->mHeader[$fieldName] = $value;
		}
	}

	public function getHeader ($fieldName) {
		if ('Cookie' == $fieldName) {
			return $this->mCookieMap[$fieldName];
		} else {
			return $this->mHeader[$fieldName];
		}
	}

	public function setCookie ($name, $value = "", $expire = 0, $path = "", $domain = "", $secure = false, $httponly = false) {
		$map = array();
		if (0 != $expire) {
			$map['expire'] = $expire;
		}
		if ('' !== $path) {
			$map['path'] = $path;
		}
		if ('' !== $domain) {
			$map['domain'] = $domain;
		}
		if (true === $secure) {
			$map['secure'] = $secure;
		}
		if (true === $httponly) {
			$map['httponly'] = $httponly;
		}
		if (empty($map)) {
			$this->mCookieMap[$name] = $value;
		} else {
			$this->mCookieMap[$name] = $map;
		}
	}

	public function setContentType ($type) {
		$this->mHeader['Content-Type'] = $type;
	}

	public function getContentType () {
		return $this->mHeader['Content-Type'];
	}

	public function getCookie ($name) {
		return $this->mCookieMap[$name];
	}

	public function getAllCookie () {
		return $this->mCookieMap;
	}

	public function setBodyMap ($map) {
		$this->mBodyMap = $map;
	}

	public function setBody ($body) {
		$this->mBody = $body;
	}

	public function addBody ($str) {
		$this->mBody .= $str;
	}

	public function getBody () {
		return $this->mBody;
	}

	public function getContentLength () {
		return $this->mHeader['Content-Length'];
	}

	protected function packOneCookie ($key, $cookie) {
		$s = '';
		
		if (is_string($cookie)) {
			$s = "Set-Cookie: $key=" . urlencode($cookie) . "\r\n";
		} else {
			$s = "Set-Cookie: $key=" . urlencode($cookie['value']) . "; ";
			if (0 != $cookie['expire']) {
				$s .= date('D, d-M-Y H:i:s e', $cookie['expire']);
			}
			if ('' !== $cookie['path']) {
				$s .= $cookie['path'];
			}
			if ('' !== $cookie['domain']) {
				$s .= $cookie['domain'];
			}
			if (true === $cookie['secure']) {
				$s .= 'secure';
			}
			if (true === $cookie['httponly']) {
				$s .= 'httponly';
			}
			
			$s .= "\r\n";
		}
	}
}