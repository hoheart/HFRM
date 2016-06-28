<?php

namespace Framework;

interface IHttpRequest {

	/**
	 * 取得通过?name=value方式传递的值
	 *
	 * @param string $name        	
	 */
	public function get ($name);

	/**
	 * 取得http请求的方法
	 */
	public function getMethod ();

	/**
	 * getRequestURI方法的别名
	 */
	public function getURI ();

	/**
	 * 取得请求的URI
	 */
	public function getRequestURI ();

	/**
	 * 判断是否为ajax请求
	 */
	public function isAjaxRequest ();

	/**
	 * 取得指定的header值
	 *
	 * @param string $fieldName        	
	 */
	public function getHeader ($fieldName);

	/**
	 * 取得客户端IP
	 */
	public function getClientIP ();

	/**
	 * 取得所有get方法能获取到的键值对
	 */
	public function getAllParams ();

	/**
	 * 取得指定名称的cookie
	 *
	 * @param string $name        	
	 */
	public function getCookie ($name);

	/**
	 * 取得所有getCookie防范能取得的键值对
	 */
	public function getAllCookie ();
}