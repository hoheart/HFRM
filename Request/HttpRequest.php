<?php

namespace Framework\Request;

use Framework\Request\IRequest;

class HttpRequest implements IRequest {
	
	/**
	 * 请求的uri
	 *
	 * @var string
	 */
	protected $mResource = '';
	
	/**
	 * 请求的主体，对应php的$_REQUEST全局变量
	 *
	 * @var array
	 */
	protected $mBody = null;

	public function __construct ($needParse = false) {
		if ($needParse) {
			$this->parse();
		}
	}

	protected function parse () {
		$this->mBody = $_REQUEST;
		$this->mResource = $_SERVER['REQUEST_URI'];
	}

	public function setBody ($body) {
		$this->mBody = $body;
	}

	public function get ($name) {
		if (array_key_exists($name, $this->mBody)) {
			return $this->mBody[$name];
		} else {
			return $_FILES[$name];
		}
	}

	public function getURI () {
		return $this->getResource();
	}

	public function getResource () {
		if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // IIS
			$requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
		} elseif (isset($_SERVER['REQUEST_URI'])) {
			$requestUri = $_SERVER['REQUEST_URI'];
			if ($requestUri !== '' && $requestUri[0] !== '/') {
				$requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
			}
		} elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0 CGI
			$requestUri = $_SERVER['ORIG_PATH_INFO'];
			if (! empty($_SERVER['QUERY_STRING'])) {
				$requestUri .= '?' . $_SERVER['QUERY_STRING'];
			}
		} else {
			throw new \Exception('Unable to determine the request URI.');
		}
		
		$uri = urldecode($requestUri);
		
		return $uri;
	}

	public function getScriptName () {
		return $_SERVER['PHP_SELF'];
	}

	static public function isAjaxRequest () {
		return ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
				 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	}

	public function getHeader ($fieldName) {
		return $_SERVER['HTTP_' . strtoupper($fieldName)];
	}

	public function isHttp () {
		return true;
	}

	public function isCli () {
		return false;
	}

	public function getClientIP () {
		$IPaddress = '';
		
		if (isset($_SERVER)) {
			if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
				$IPaddress = $_SERVER["HTTP_X_FORWARDED_FOR"];
			} else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
				$IPaddress = $_SERVER["HTTP_CLIENT_IP"];
			} else {
				$IPaddress = $_SERVER["REMOTE_ADDR"];
			}
		} else {
			if (getenv("HTTP_X_FORWARDED_FOR")) {
				$IPaddress = getenv("HTTP_X_FORWARDED_FOR");
			} else if (getenv("HTTP_CLIENT_IP")) {
				$IPaddress = getenv("HTTP_CLIENT_IP");
			} else {
				$IPaddress = getenv("REMOTE_ADDR");
			}
		}
		
		return $IPaddress;
	}

	public function getAllParams () {
		return $this->mBody;
	}

	public function getMethod () {
		return $_SERVER['REQUEST_METHOD'];
	}
}