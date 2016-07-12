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

	public function packRet ($obj, $err = array()) {
		$node = array(
			'errcode' => 0,
			'errstr' => '',
			'data' => $obj
		);
		if (! empty($err)) {
			$node = $err;
		}
		return json_encode($node);
	}

	public function getContentType () {
		return 'application/json; charset=utf-8';
	}
}