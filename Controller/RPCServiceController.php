<?php

namespace Framework\Controller;

use Framework\Controller;
use Framework\Request\IRequest;
use Framework\Module\ModuleManager;

class RPCServiceController extends Controller {

	public function serve (IRequest $req) {
		$data = $req->get('d');
		$req = json_decode($data, true);
		$moduleAlias = $req['module'];
		$apiName = $req['api'];
		$methodName = $req['method'];
		$parameterArr = $req['parameters'];
		
		$module = ModuleManager::Instance()->get($moduleAlias);
		$service = $module->getService($apiName);
		$ret = call_user_func_array(array(
			$service,
			$methodName
		), $parameterArr);
		
		$this->setJsonView($ret);
	}
}