<?php

namespace Framework\RPCProtocol;

interface IRPCProtocol {

	static public function Instance ();

	/**
	 * 解析远程调用的参数
	 *
	 * @param string $str        	
	 */
	public function parseArgs ($str, $apiName, $methodName);

	/**
	 * 打包远程调用的返回结果
	 *
	 * @param object $obj        	
	 */
	public function packRet ($obj);
}