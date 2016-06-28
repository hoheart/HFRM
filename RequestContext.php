<?php

namespace Framework;

use Framework\Request\HttpRequest;
use Framework\Output\IOutputStream;
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
	 * @var HttpRequest
	 */
	public $request = null;
	
	/**
	 *
	 * @var IOutputStream
	 */
	public $output = null;
	
	/**
	 *
	 * @var map
	 */
	protected $mObjectMap = array();

	public function __construct (IHttpRequest $req, IOutputStream $out) {
		$this->request = $req;
		$this->output = $out;
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