<?php

namespace Framework\Module;

use Framework\Exception\ConfigErrorException;
use Framework\Config;
use Framework\Exception\APINotAvailableException;
use Framework\App;
use Framework;
use Framework\Exception\NotImplementedException;
use Framework\Exception\RPCServiceErrorException;

class ServiceAgent implements IModuleService {
	
	/**
	 * 模块别名
	 *
	 * @var string
	 */
	protected $mModuleAlias = '';
	
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
	
	/**
	 * 本地服务实例
	 *
	 * @var IModuleService
	 */
	protected $mLocalInstance = null;

	public function __construct () {
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see \Framework\Module\IModuleService::init()
	 */
	public function init (array $conf) {
		$this->mModuleAlias = $conf['moduleAlias'];
		$this->mModulePath = $conf['modulePath'];
		$this->mInterfaceName = $conf['className'];
	}

	public function start () {
	}

	public function stop ($normal = true) {
	}

	public function __call ($name, $arguments) {
		$serverUrl = $this->choseRemoteServer($this->mModulePath);
		
		if (self::isRemote($serverUrl)) {
			return $this->makeRemoteCall($serverUrl, $this->mInterfaceName, $name, $arguments);
		} else {
			if (null == $this->mLocalInstance) {
				// 从配置文件取得接口对应的实例类
				$clsName = Config::Instance()->getModuleConfig($this->mModuleAlias, 
						'moduleService.' . $this->mInterfaceName);
				if (empty($clsName)) {
					throw new ConfigErrorException('can not found the API :' . $this->mInterfaceName);
				}
				
				App::Instance()->useModule($this->mModuleAlias);
				$this->mLocalInstance = new $clsName();
				$topApi = 'Framework\Module\IModuleService';
				if (! $this->mLocalInstance instanceof $topApi) {
					if (Config::Instance()->get('app.debug')) {
						throw new ConfigErrorException('Class:' . $clsName . ' is not a implement of ' . $topApi);
					} else {
						throw new APINotAvailableException('can not find the API:' . $topApi);
					}
				}
				
				// 判断所实现的接口有没有该方法
				$arr = class_implements($this->mLocalInstance);
				$exists = false;
				foreach ($arr as $api) {
					if (method_exists($api, $name)) {
						$exists = true;
						break;
					}
				}
				if (! $exists) {
					throw new NotImplementedException(
							'no interface has method: ' . $name . ',interfaces: ' . implode(',', $arr));
				}
				
				$this->mLocalInstance->init(
						array(
							'moduleAlias' => $this->mModuleAlias,
							'modulePath' => $this->mModulePath
						));
				$this->mLocalInstance->start();
			}
			
			$s = $this->mLocalInstance;
			
			return call_user_func_array(array(
				$s,
				$name
			), $arguments);
		}
	}

	static public function isRemote ($path) {
		if (0 === strcasecmp('http://', substr($path, 0, 7))) {
			return true;
		} else {
			return false;
		}
	}

	protected function makeRemoteCall ($url, $apiName, $methodName, $arguments) {
		$rpParam = array(
			'module' => $this->mModuleAlias,
			'api' => $apiName,
			'method' => $methodName,
			'parameters' => $arguments
		);
		
		$httpBody = 'd=' . urlencode(json_encode($rpParam));
		
		$strCookies = http_build_query($_COOKIE, '', ';');
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_COOKIE, $strCookies);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'HTTP_X_REQUESTED_WITH: xmlhttprequest'
		));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $httpBody);
		$resp = curl_exec($ch);
		
		if (false === $resp) {
			$e = new RPCServiceErrorException(curl_error($ch));
			curl_close($ch);
			throw $e;
		}
		curl_close($ch);
		
		$oResp = json_decode($resp);
		if (0 != $oResp->errcode) {
			throw new \Exception($oResp->errstr, $oResp->errcode);
		}
		
		return $oResp->data;
	}

	protected function choseRemoteServer ($urlArr) {
		if (! is_array($urlArr)) {
			$urlArr = array(
				$urlArr
			);
		}
		
		$microTime = microtime(true) * 10000;
		$serverCount = count($urlArr);
		
		$targetUrl = $urlArr[$microTime % $serverCount];
		
		return $targetUrl;
	}
}