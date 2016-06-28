<?php

namespace Framework\FlatBuffers;

use Framework\RPCProtocol\IRPCProtocol;

class FlatBuffersRPCProtocol implements IRPCProtocol {

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$cls = get_called_class();
			$me = new $cls();
		}
		
		return $me;
	}

	public function parseArgs ($binArgs, $interfaceName, $methodName) {
		$clsName = "$interfaceName\\$methodName";
		$fbsObj = new $clsName();
		
		$fbsMethodName = "getRootAs{$methodName}_req";
		$fbsObj->$fbsMethodName($binArgs);
		
		$argArr = array();
		$argArrCount = $fbsObj->getParamsLength();
		for ($i = 0; $i < $argArrCount; ++ $i) {
			$argName = $fbsObj->getParams($i);
			$argName = 'get' . ucfirst($argName);
			$argArr[] = $fbsObj->$argName();
		}
		
		return $argArr;
	}

	public function packRet ($obj) {
		//TODO
	}
}