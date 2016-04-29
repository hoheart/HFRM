<?php

namespace Framework\Controller;

use Framework\Controller;
use Framework\Request\IRequest;
use Framework\Module\ModuleManager;
use Framework\Exception\SystemErrcode;
use Framework\View\View;

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
			$this->setView('common::Common.frame', View::VIEW_TYPE_JSON);
			$this->assign('errcode', SystemErrcode::RPCServiceError);
			$this->assign('errmsg', $e->getMessage() . 'On ' . $e->getFile() . ' : ' . $e->getLine());
		}
	}
}