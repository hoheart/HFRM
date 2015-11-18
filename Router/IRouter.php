<?php

namespace Framework\Router;

use Framework\Request\IRequest;

interface IRouter {

	static public function Instance ();

	/**
	 *
	 * @param IRequest $req        	
	 * @return array 第一个值为controller类全名，第二个值为action名称
	 */
	public function getRoute (IRequest $req);
}