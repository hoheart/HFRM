<?php

namespace Framework;

use Framework\HFC\Exception\ParameterErrorException;

/**
 * 请求的上下文，包括http请求和输出，主要用在编写异步代码时，解决重入问题，作为参数传递用。
 *
 * @author Hoheart
 *        
 */
class RequestContext {
	
	/**
	 *
	 * @var IHttpRequest
	 */
	public $request = null;
	
	/**
	 *
	 * @var IHttpResponse
	 */
	public $response = null;
	
	/**
	 *
	 * @var map
	 */
	protected $mObjectMap = array();

	public function __construct (IHttpRequest $req, IHttpResponse $resp) {
		$this->request = $req;
		$this->response = $resp;
	}

	public function add ($key, $obj) {
		if (array_key_exists($key, $this->mObjectMap)) {
			throw ParameterErrorException('the context key exists: ' . $key);
		}
		
		$this->mObjectMap[$key] = $obj;
	}

	public function get ($key) {
		return $this->mObjectMap[$key];
	}

	public function respond ($obj) {
		App::respond($this, $obj);
	}
}