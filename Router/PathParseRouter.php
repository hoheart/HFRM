<?php

namespace Framework\Router;

use Framework\Request\IRequest;
use Framework\Exception\RequestErrorException;
use Framework\Config;
use Framework\Module\ModuleManager;
use Framework\Exception\ConfigErrorException;
use Framework\Exception\NotFoundHttpException;

/**
 * 解析请求路径，对应到Controller和Action的的路由器
 *
 * 规则：路由分为四段：模块别名/路径/Controller名/Action名
 * 第一段为模块别名，如果模块不存在，则用配置的默认模块；
 * 第二段为路径，如果路径不存在，则路径为空；
 * 第三段为控制器名，控制器名加上“Controller.php”字样，为文件名；如果文件不存在，用模块名作为控制器名；
 * 第四段为Action名，如果action不存在，则默认为index。
 *
 * @example 下面都假设控制器路径为Controller文件夹，defaultModule的值为index
 *          /　　　　Index\Controller\Index::index。
 *          /user　　如果user模块存在，则为User\Controller\UserController::index
 *          如果user模块不存在，路径存在,则为：Index\Controller\User\IndexController::index
 *          如果user模块不存在，路径不存在，则为:Index\Controller\UserController::index
 *          其他情况视为请求错误请求。
 *         
 * @author Hoheart
 *        
 */
class PathParseRouter implements IRouter {
	
	/**
	 * 本次请求的路由
	 *
	 * @var array
	 */
	protected $mRedirection = null;

	static public function Instance () {
		static $me = null;
		if (null == $me) {
			$me = new PathParseRouter();
		}
		
		return $me;
	}

	public function getCurrentRoute (IRequest $req) {
		return $this->mRedirection;
	}

	public function getRoute (IRequest $req) {
		$moduleAlias = '';
		$ctrlName = '';
		$actionName = '';
		
		// 找模块别名
		$uri = $this->getRequestUri($req);
		$uri = preg_replace('/\/{2,}/', '/', $uri);
		$arr = explode('/', $uri);
		if (empty($arr[0])) {
			array_shift($arr);
		}
		$moduleAlias = $arr[0];
		$mm = ModuleManager::Instance();
		if (! $mm->isModuleEnable($moduleAlias)) {
			$moduleAlias = Config::Instance()->get('app.defaultModule');
			if (! ModuleManager::Instance()->isModuleEnable($moduleAlias)) {
				throw new RequestErrorException('the request url is not found:' . $uri);
			}
		} else {
			array_shift($arr);
		}
		
		// 已备后面检查路径存不存在用
		ModuleManager::Instance()->preloadModule($moduleAlias);
		
		// 找路径
		list ($moduleName, $ctrlName, $ctrlFilePath) = $this->parsePathSection($moduleAlias, $arr);
		
		// 找控制器名
		$section = ucfirst($arr[0]);
		$ctrlFilePath .= $section . 'Controller.php';
		if (empty($section) || ! file_exists($ctrlFilePath)) {
			$ctrlName = Config::Instance()->getModuleConfig($moduleAlias, 'app.defaultController');
			if (empty($ctrlName)) {
				throw new RequestErrorException('the request url is not found:' . $uri);
			}
		} else {
			$ctrlName .= $section . 'Controller';
			array_shift($arr);
		}
		if (! class_exists($ctrlName)) {
			throw new NotFoundHttpException();
		}
		
		// action
		$actionName = $this->parseAction($arr);
		if (! method_exists($ctrlName, $actionName)) {
			throw new NotFoundHttpException();
		}
		
		$this->mRedirection = array(
			$moduleAlias,
			$ctrlName,
			$actionName
		);
		
		return $this->mRedirection;
	}

	protected function parseAction ($arr) {
		$actionName = $arr[0];
		
		$pos = strpos($actionName, '?');
		if (false !== $pos) {
			$actionName = substr($actionName, 0, $pos);
		}
		
		$pos = strpos($actionName, '.');
		if (false !== $pos) {
			$actionName = substr($actionName, 0, $pos);
		}
		
		$pos = strpos($actionName, '#');
		if (false !== $pos) {
			$actionName = substr($actionName, 0, $pos);
		}
		
		if (empty($actionName)) {
			$actionName = 'index';
		}
		
		return $actionName;
	}

	protected function parsePathSection ($moduleAlias, &$arr) {
		$moduleName = ModuleManager::Instance()->getModuleName($moduleAlias);
		$moduleCtrlPath = Config::Instance()->getModuleConfig($moduleAlias, 'app.controllerDir');
		if (empty($moduleCtrlPath)) {
			$moduleCtrlPath = 'Controller' . DIRECTORY_SEPARATOR;
		}
		$ctrlName = $moduleName . '\\' . str_replace('/', '\\', $moduleCtrlPath);
		
		$ctrlFilePath = $moduleName . DIRECTORY_SEPARATOR . $moduleCtrlPath;
		while (count($arr) > 0) {
			$section = ucfirst($arr[0]);
			if (! file_exists($ctrlFilePath . $section)) {
				break;
			} else {
				$ctrlName .= $section . '\\';
				array_shift($arr);
			}
			
			$ctrlFilePath .= $section . DIRECTORY_SEPARATOR;
		}
		
		return array(
			$moduleName,
			$ctrlName,
			$ctrlFilePath
		);
	}

	protected function getRequestUri (IRequest $req) {
		$retUri = '/';
		
		$uri = $req->getResource();
		$baseUrl = Config::Instance()->get('app.baseUrl');
		$baseUrlArr = parse_url($baseUrl);
		$path = $baseUrlArr['path']; // path是域名之后，问号之前的东西
		if (! empty($path)) {
			$pos = strpos($uri, $path);
			// 配置的baseUrl只能是url的开头部分。1表示baseUrl里没有带/
			if (0 != $pos && 1 != $pos) {
				throw new ConfigErrorException('app.baseUrl config error.');
			}
			
			$uri = substr($uri, strlen($path));
		}
		
		if (null != $uri) {
			$retUri = $uri;
		}
		
		return $retUri;
	}
}