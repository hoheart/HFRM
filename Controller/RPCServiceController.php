<?php

namespace Framework\Controller;

use Framework\Controller;
use Framework\Request\IRequest;
use Framework\Module\ModuleManager;
use Framework\Exception\SystemErrcode;

class RPCServiceController extends Controller {

	public function serve (IRequest $req) {
		$data = $req->get('d');
		$reqArr = unserialize($data);
		$moduleAlias = $reqArr['module'];
		$apiName = $reqArr['api'];
		$methodName = $reqArr['method'];
		$parameterArr = $reqArr['parameters'];
		
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
			$this->setErrorJsonView(SystemErrcode::RPCServiceError, $errstr);
		}
	}
}