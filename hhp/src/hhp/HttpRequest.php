<?php

namespace hhp;

use hhp\IRequest;

class HttpRequest implements IRequest {
	
	/**
	 * 请求的主体，对应php的$_REQUEST全局变量
	 *
	 * @var array
	 */
	protected $mBody = null;

	public function __construct () {
	}

	public function setBody ($body) {
		$this->mBody = $body;
	}

	public function getVal ($name) {
		return $this->mBody[$name];
	}

	public function setVal ($name, $value) {
		$this->mBody[$name] = $value;
		
		return $this;
	}

	public function getRequestUri () {
		$uri = urldecode($_SERVER['REQUEST_URI']);
		return $uri;
	}
}