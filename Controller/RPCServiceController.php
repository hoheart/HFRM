<?php

namespace Framework\Controller;

use Framework\Config;
use Framework\Exception\NotFoundHttpException;
use Framework\App;
use Framework\RequestContext;
use Framework\IHttpRequest;

class RPCServiceController {
	
	/**
	 * 已经实例化的服务的列表
	 *
	 * @var map
	 */
	protected $mServiceMap = array();

	public function serve (RequestContext $context) {
		$data = '';
		
		list ($apiName, $clsName, $interfaceName, $methodName, $service) = $this->getService($context->request);
		if (null === $service) {
			throw new NotFoundHttpException();
		}
		
		$binArgs = $context->request->get('a');
		$rpcp = App::Instance()->getRpcProtocol();
		$arguments = $rpcp->parseArgs($binArgs, $apiName, $methodName);
		$ret = call_user_func_array(array(
			$service,
			$methodName
		), $arguments);
		if (! $context->get('hasResponded')) {
			App::Respond($context, $ret);
		}
	}

	protected function getService (IHttpRequest $req) {
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
			return array();
		}
		
		$clsName = Config::Instance()->get('moduleService.' . $apiName);
		// 判断有没有配置apiName对应的类名
		if (empty($clsName)) {
			return array();
		}
		// 判断类名存不存在
		if (! class_exists($clsName)) {
			return array();
		}
		if (! method_exists($clsName, $methodName)) {
			return array();
		}
		
		// 判断类实现的接口是否有该方法，避免出现访问没有公开的接口
		$arr = class_implements($clsName);
		$interfaceName = current($arr);
		if (! method_exists($interfaceName, $methodName)) {
			return array();
		}
		
		$s = null;
		if (array_key_exists($apiName, $this->mServiceMap)) {
			$s = $this->mServiceMap[$apiName];
		} else {
			$s = new $clsName();
			$this->mServiceMap[$apiName] = $s;
		}
		
		return array(
			$apiName,
			$clsName,
			$interfaceName,
			$methodName,
			$s
		);
	}
}