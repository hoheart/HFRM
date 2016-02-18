<?php

namespace Framework\Request;

interface IRequest {
	
	/**
	 * method
	 *
	 * @var string
	 */
	const REQUEST_METHOD_GET = 'GET';
	const REQUEST_METHOD_POST = 'POST';

	public function isHttp ();

	public function isCli ();

	public function getResource ();

	/**
	 * 根据键值取得请求内容的值
	 *
	 * @param string $key        	
	 */
	public function get ($key);

	public function getAllParams ();

	public function getMethod ();
}