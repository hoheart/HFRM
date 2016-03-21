<?php

namespace Framework\Response;

interface IHttpResponse extends IResponse {

	public function status ($code);

	public function getStatus ();

	public function header ($key, $val);

	public function getHeader ($key);

	public function getAllHeader ();

	public function cookie ($key, $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = false);

	public function getCookie ($key);

	public function getAllCookie ();

	public function addBody ($content);

	public function getBody ();
}