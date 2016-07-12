<?php

namespace Framework;

interface IHttpResponse {

	/**
	 * 设置响应的状态码
	 *
	 * @param int $code        	
	 */
	public function setStatusCode ($code);

	/**
	 * 设置原因
	 *
	 * @param string $reason        	
	 */
	public function setReasonPhrase ($reason);

	/**
	 * 取得指定的header值
	 *
	 * @param string $fieldName        	
	 * @param string $value        	
	 */
	public function setHeader ($fieldName, $value);

	/**
	 * 取得指定名称的cookie
	 *
	 * @param string $name        	
	 * @param string $value        	
	 */
	public function setCookie ($key, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false);

	/**
	 * 设置响应内容
	 *
	 * @param string $body        	
	 */
	public function setBody ($body);
}