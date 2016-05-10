<?php

namespace Framework\Controller;

use Framework\Controller;
use Framework\Request\IRequest;
use Framework\Module\ModuleManager;

class RPCServiceController extends Controller {

	public function serve (IRequest $req) {
		$data = $req->get('d');
		$req = json_decode($data);
		$moduleAlias = $req->module;
		$apiName = $req->api;
		$methodName = $req->method;
		$parameterArr = $req->parameters;
		
		try {
			$module = ModuleManager::Instance()->get($moduleAlias);
			$service = $module->getService($apiName);
			$ret = call_user_func_array(array(
				$service,
				$methodName
			), $parameterArr);
			
			$this->setJsonView($ret);
		} catch (\Exception $e) {
			$errstr = $e->getMessage() . 'On ' . $e->getFile() . ' : ' . $e->getLine();
			$this->setErrorJsonView($e->getCode(), $errstr);
		}
	}
}