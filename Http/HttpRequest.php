<?php

namespace Framework\Http;

use Framework\IHttpRequest;

class HttpRequest extends HttpMessage implements IHttpRequest {
	
	/**
	 *
	 * @var string $mMethod
	 */
	protected $mMethod = 'GET';
	
	/**
	 *
	 * @var string $mUri
	 */
	protected $mUri = '/';
	
	/**
	 * 通过?号传递的参数
	 *
	 * @var map $mQueryParamMap
	 */
	protected $mQueryParamMap = array();

	public function __construct ($url) {
		$this->setURI($url);
		
		parent::__construct();
	}

	public function set ($name, $value) {
		$this->mQueryParamMap[$name] = $value;
	}

	public function get ($name) {
		return $this->mQueryParamMap[$name];
	}

	public function setMethod ($method) {
		$this->mMethod = $method;
	}

	public function getMethod () {
		return $this->mMethod;
	}

	public function setURI ($uri) {
		$this->mUri = $uri;
		
		$urlArr = parse_url($uri);
		$host = $urlArr['host'];
		if (! empty($urlArr['port'])) {
			$host .= ':' . $urlArr['port'];
		}
		$this->setHeader('Host', $host);
		
		$pos = strpos('?', $uri);
		if (false !== $pos) {
			$str = substr($uri, $pos + 1);
			$arr = explode('=', $str);
			
			foreach ($arr as $key => $val) {
				$this->mQueryParamMap[$key] = urldecode($val);
			}
		}
	}

	public function getURI () {
		return $this->mUri;
	}

	public function setRequestURI ($uri) {
		$this->setURI($uri);
	}

	public function getRequestURI () {
		return $this->getURI();
	}

	public function isAjaxRequest () {
		return strtolower($this->getHeader('HTTP_X_REQUESTED_WITH')) == 'xmlhttprequest';
	}

	public function getClientIP () {
		$IPaddress = $this->getHeader('HTTP_X_FORWARDED_FOR');
		if (! empty($IPaddress)) {
			return $IPaddress;
		}
		
		$IPaddress = $this->getHeader('HTTP_CLIENT_IP');
		if (! empty($IPaddress)) {
			return $IPaddress;
		}
		
		$IPaddress = $this->getHeader('REMOTE_ADDR');
		if (! empty($IPaddress)) {
			return $IPaddress;
		}
	}

	public function getAllParams () {
		return $this->mQueryParamMap;
	}

	public function pack () {
		$s = $this->mMethod . ' ' . $this->mUri . ' ' . $this->mVersion . "\r\n";
		
		foreach ($this->mHeader as $key => $val) {
			$s .= "$key: $val\r\n";
		}
		
		$body = '';
		foreach ($this->mBodyMap as $key => $val) {
			$body .= "$key=";
			if (self::CONTENT_TYPE_URLENCODED == $this->mContentType) {
				$body .= urlencode($val);
			} else {
				$body .= $val;
			}
		}
		$s .= 'Content-Length: ' . strlen($body) . "\r\n";
		
		foreach ($this->mCookieMap as $key => $val) {
			$s .= $this->packOneCookie($key, $val);
		}
		
		$s .= "\r\n";
		
		$s .= $body;
		
		return $s;
	}
}