<?php

namespace Framework\RPCProtocol;

class JsonRPCProtocol implements IRPCProtocol {

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$cls = get_called_class();
			$me = new $cls();
		}
		
		return $me;
	}

	public function parseArgs ($str, $apiName, $methodName) {
		return json_decode($str);
	}

	public function packRet ($obj) {
		$node = array(
			'errcode' => 0,
			'errstr' => '',
			'data' => $obj
		);
		return json_encode($node);
	}
}