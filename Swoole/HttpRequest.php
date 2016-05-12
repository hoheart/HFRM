<?php

namespace Framework\Swoole;

use Framework\Request\IRequest;
use Framework\Exception\NotImplementedException;

class HttpRequest implements IRequest {
	
	/**
	 *
	 * @var swoole_http_request
	 */
	protected $mRequest = null;
	
	/**
	 *
	 * @var array $mAllParam
	 */
	protected $mAllParam = array();

	public function __construct ($req) {
		$this->mRequest = $req;
	}

	public function get ($name) {
		$post = $this->mRequest->post;
		$get = $this->mRequest->get;
		$cookie = $this->mRequest->cookie;
		if (is_array($post) && array_key_exists($name, $post)) {
			return $post[$name];
		} else if (is_array($get) && array_key_exists($name, $get)) {
			return $get[$name];
		} else if (is_array($cookie) && array_key_exists($name, $cookie)) {
			return $cookie[$name];
		}
		
		return null;
	}

	public function setBody ($content) {
		throw new NotImplementedException();
	}

	public function getURI () {
		return $this->getResource();
	}

	public function getResource () {
		$uri = urldecode($this->mRequest->server['request_uri']);
		return $uri;
	}

	public function getScriptName () {
		throw new NotImplementedException();
	}

	public function isAjaxRequest () {
		return ! empty($this->mRequest->header['http_x_requested_with']) &&
				 strtolower($this->mRequest->header['http_x_requested_with']) == 'xmlhttprequest';
	}

	public function getHeader ($fieldName) {
		$fieldName = strtolower($fieldName);
		return $this->mRequest->header[$fieldName];
	}

	public function isHttp () {
		return true;
	}

	public function isCli () {
		return false;
	}

	public function getClientIP () {
		$IPaddress = '';
		
		if (isset($this->mRequest->header)) {
			if (isset($this->mRequest->header["http_x_forwarded_for"])) {
				$IPaddress = $this->mRequest->header["http_x_forwarded_for"];
			} else if (isset($this->mRequest->header["http_client_ip"])) {
				$IPaddress = $this->mRequest->header["http_client_ip"];
			} else if (isset($this->mRequest->server['remote_addr'])) {
				$IPaddress = $this->mRequest->server["remote_addr"];
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
		if (empty($this->mAllParam)) {
			if (is_array($this->mRequest->get)) {
				$this->mAllParam = array_merge($this->mAllParam, $this->mRequest->get);
			}
			if (is_array($this->mRequest->post)) {
				$this->mAllParam = array_merge($this->mAllParam, $this->mRequest->post);
			}
			if (is_array($this->mRequest->cookie)) {
				$this->mAllParam = array_merge($this->mAllParam, $this->mRequest->cookie);
			}
		}
		
		return $this->mAllParam;
	}

	public function getMethod () {
		return $this->mRequest->server['request_method'];
	}

	public function getHost () {
		return $this->mRequest->header['host'];
	}
}