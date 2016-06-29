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
			$node['errcode'] = $err['errcode'];
			$node['errstr'] = $err['errstr'];
			$errdetail = $err['errDetail'];
			$node['errDetail'] = $errdetail;
		}
		return json_encode($node);
	}
}