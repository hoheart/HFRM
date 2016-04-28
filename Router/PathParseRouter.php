<?php

namespace Framework\Router;

use Framework\Request\IRequest;
use Framework\Config;
use Framework\Module\ModuleManager;
use Framework\Exception\NotFoundHttpException;
use Framework\Exception\ModuleNotAvailableException;

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
		
		// 解析路径
		$uri = $req->getUri($req);
		$uri = preg_replace('/\/{1,}/', '\\', $uri);
		$arr = explode('\\', $uri);
		if (empty($arr[0])) {
			array_shift($arr);
		}
		
		// 分析出模块别名和action名字，剩下的就是controller名。
		$moduleAlias = $arr[0];
		$mm = ModuleManager::Instance();
		if (! $mm->isModuleEnable($moduleAlias)) {
			$moduleAlias = Config::Instance()->get('app.defaultModule');
			if (! ModuleManager::Instance()->isModuleEnable($moduleAlias)) {
				throw new ModuleNotAvailableException();
			}
		} else {
			array_shift($arr);
		}
		$actionName = array_pop($arr);
		// Controller名
		$ctrlName = $mm->getModuleName($moduleAlias) . '\\Controller';
		foreach ($arr as $section) {
			$ctrlName .= '\\' . ucfirst($section);
		}
		$ctrlName .= 'Controller';
		
		if (! $this->exists($moduleAlias, $ctrlName, $actionName)) {
			throw new NotFoundHttpException();
		}
		
		$route = array(
			$moduleAlias,
			$ctrlName,
			$actionName
		);
		$this->mRedirection = $route;
		return $route;
	}

	public function exists ($moduleAlias, $ctrlName, $actionName) {
		// 检查是否存在
		ModuleManager::Instance()->preloadModule($moduleAlias);
		if (! class_exists($ctrlName)) {
			return false;
		}
		if (! method_exists($ctrlName, $actionName)) {
			return false;
		}
		
		return true;
	}
}