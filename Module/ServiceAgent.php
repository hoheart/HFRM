<?php

namespace Framework\Module;

use Framework\App;
use Framework;
use Framework\Exception\RPCServiceErrorException;
use Framework\App\AsyncHttpClient;
use Framework\Http\HttpRequest;

class ServiceAgent {
	
	/**
	 * 模块的路径配置
	 *
	 * @var string
	 */
	protected $mModulePath = '';
	
	/**
	 * 服务对应的类名
	 *
	 * @var string
	 */
	protected $mInterfaceName = '';

	public function __construct ($path, $apiName) {
		$this->mModulePath = $path;
		$this->mInterfaceName = $apiName;
	}

	public function __call ($name, $arguments) {
		return $this->makeRemoteCall($this->mInterfaceName, $name, $arguments);
	}

	protected function asyncCall ($serverUrl, $methodName, $arguments, $callback) {
		$r = new HttpRequest($serverUrl);
		$r->setURI('/' . $this->mInterfaceName . '/' . $methodName);
		$r->set('a', json_encode($arguments));
		$c = new AsyncHttpClient();
		$c->exec($r, $callback);
	}

	protected function makeRemoteCall ($apiName, $methodName, $arguments) {
		$serverUrl = $this->choseRemoteServer($this->mModulePath);
		if ('http://' !== substr($serverUrl, 0, 7)) {
			$serverUrl .= 'http://' . $serverUrl;
		}
		
		$lastArg = end($args);
		if ($lastArg instanceof \Closure) {
			$callback = array_pop($arguments);
			$this->asyncCall($serverUrl, $methodName, $arguments, $callback);
			
			return;
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $serverUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'HTTP_X_REQUESTED_WITH: xmlhttprequest'
		));
		
		$cookieArr = App::Instance()->getRequest()->getAllCookie();
		$strCookies = http_build_query($cookieArr, '', ';');
		curl_setopt($ch, CURLOPT_COOKIE, $strCookies);
		
		$httpBody = 'a=' . urlencode(json_encode($arguments));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $httpBody);
		
		$resp = curl_exec($ch);
		
		if (false === $resp) {
			$e = new RPCServiceErrorException(curl_error($ch));
			curl_close($ch);
			throw $e;
		}
		curl_close($ch);
		
		$oResp = json_decode($resp, true);
		if (0 != $oResp['errcode']) {
			throw new \Exception($oResp['errstr'], $oResp['errcode']);
		}
		
		return $oResp['data'];
	}

	protected function choseRemoteServer ($urlArr) {
		if (! is_array($urlArr)) {
			$urlArr = array(
				$urlArr
			);
		}
		
		$microTime = explode(' ', microtime())[0] * 1000000;
		$serverCount = count($urlArr);
		
		$targetUrl = $urlArr[$microTime % $serverCount];
		
		return $targetUrl;
	}

	static public function waitAll () {
		AsyncHttpClient::wait();
	}
}