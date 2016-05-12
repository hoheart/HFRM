<?php

namespace hhp;

interface IRequest {

	public function isHttp ();

	public function isCli ();

	public function getResource ();

	/**
	 * 根据键值取得请求内容的值
	 *
	 * @param string $key        	
	 */
	public function get ($key);
}
?>