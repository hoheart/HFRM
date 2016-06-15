<?php

namespace Framework\Controller;

use Framework\Request\IRequest;
use Framework\Output\IOutputStream;
use Framework\Config;
use Framework\Exception\NotFoundHttpException;
use Framework\App;

class RPCServiceController {
	
	/**
	 * 已经实例化的服务的列表
	 *
	 * @var map
	 */
	protected $mServiceMap = array();

	public function serve (IRequest $req, IOutputStream $out) {
		$binArgs = $req->get('a');
		$arguments = $this->parseFlatbuffers($binArgs);
		
		$callback = $this->getService($req);
		
		$ret = call_user_func_array($callback, $arguments);
		if (null != $ret) {
			$data = $this->packFlatbuffers($ret);
			
			$out->write($data);
			$out->close();
			
			App::Instance()->stop();
		}
	}

	protected function parseFlatbuffers ($binArgs) {
	}

	protected function packFlatbuffers ($ret) {
	}

	protected function getService (IRequest $req) {
		$apiName = '';
		$methodName = '';
		$uri = $req->getUri($req);
		$uri = preg_replace('/\/{1,}/', '\\', $uri);
		$arr = explode('\\', $uri);
		if (empty($arr[0])) {
			array_shift($arr);
		}
		list ($apiName, $methodName) = $arr;
		if (empty($apiName) || empty($methodName)) {
			throw new NotFoundHttpException();
		}
		
		$clsName = Config::Instance()->get('moduleService.' . $apiName);
		if (empty($clsName)) {
			throw new NotFoundHttpException();
		}
		if (! class_exists($clsName)) {
			throw new NotFoundHttpException();
		}
		if (! method_exists($clsName, $methodName)) {
			throw new NotFoundHttpException();
		}
		
		$s = null;
		if (array_key_exists($apiName, $this->mServiceMap)) {
			$s = $this->mServiceMap[$apiName];
		} else {
			$s = new $clsName();
			$this->mServiceMap[$apiName] = $s;
		}
		
		return array(
			$s,
			$methodName
		);
	}
}