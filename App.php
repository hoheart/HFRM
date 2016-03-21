<?php

namespace Framework {

	use Framework\App\ClassLoader;
	use Framework\Request\HttpRequest;
	use Framework\Request\CliRequest;
	use Framework\Request\IRequest;
	use Framework\Module\ModuleManager;
	use HFC\Exception\ParameterErrorException;
	use Framework\Config;
	use Framework\Request\RequestFilter;
	use Framework\Router\PathParseRouter;
	use Framework\View\ViewRender;
	use Framework\Facade\Module;
	use Framework\Output\StandardOutputStream;
	use Framework\Output\IOutputStream;

	/**
	 * 框架核心类，完成路由执行控制器和Action，并集成了常用方法。
	 * 当框架所在的应用程序被启动后，整个进程里应该就一个App实例--虽然由于PHP的特性并非这样，但丝毫不影响把App类设计成单实例。
	 * 启动App的顺序：
	 * $app = App::Instance();
	 * $app->run($conf);
	 *
	 * @author Hoheart
	 *        
	 */
	class App {
		
		/**
		 * 本框架的版本
		 *
		 * @var string
		 */
		const FRAMEWORK_VERSION = '1.01';
		
		/**
		 * 整个APP的根目录
		 */
		public static $ROOT_DIR;
		
		/**
		 * 存放ClassLoader。
		 *
		 * @var ClassLoader
		 */
		protected $mClassLoader = null;
		
		/**
		 *
		 * @var ModuleManager
		 */
		protected $mModuleManager = null;
		
		/**
		 *
		 * @var IRequest
		 */
		protected $mRequest = null;
		
		/**
		 * 路由器
		 *
		 * @var IRouter
		 */
		protected $mRouter = null;
		
		/**
		 * 启动的Controller，即url中指定要访问的Controller。
		 *
		 * @var \Framework\Controller
		 */
		protected $mCurrentController = null;
		
		/**
		 * 试图渲染器
		 *
		 * @var ViewRender
		 */
		protected $mViewRender = null;
		
		/**
		 *
		 * @var IOutputStream
		 */
		protected $mOutputStream = null;

		/**
		 * 构造函数，创建ClassLoader，并调用其register2System。
		 */
		protected function __construct () {
		}

		public function init () {
			// 切换到App目录。
			chdir(self::$ROOT_DIR);
			
			$this->mClassLoader = new ClassLoader();
			$this->mClassLoader->register2System();
			
			$errorHandler = new ErrorHandler();
			$errorHandler->register2System();
			
			// 还没想好怎么处理，暂时放这儿。
			RequestFilter::RemoveSQLInjection();
			
			// 还没想好怎么处理，暂时放这儿。
			date_default_timezone_set(Config::Instance()->get('app.localTimezone'));
			
			// 防止直接echo等的输出，避免xss攻击。
			ob_start();
			
			$this->mModuleManager = ModuleManager::Instance();
			
			$routerCls = Config::Instance()->get('app.router');
			if (empty($routerCls)) {
				$routerCls = '\Framework\Router\PathParseRouter';
			}
			$this->mRouter = $routerCls::Instance();
			
			$this->mViewRender = new ViewRender();
		}

		/**
		 * 给连接池专用的初始连接池的函数，一般由swoole调用，且是swoole的主进程调用
		 * apache module 或fpm模式下不用调用该函数
		 */
		public function initPoolService () {
			$moduleAliasArr = $this->mModuleManager->getAllModuleAlias();
			foreach ($moduleAliasArr as $moduleAlias) {
				$this->getService('db', $moduleAlias);
			}
		}

		public function start () {
		}

		public function stop () {
			if (null != $this->mServiceManager) {
				$this->mServiceManager->stop();
			}
			
			$this->mModuleManager->stop();
			
			if (Config::Instance()->get('app.debugOutput')) {
				ob_flush();
				flush();
			} else {
				ob_clean();
			}
		}

		/**
		 * 启动应用程序。
		 */
		public function run (IRequest $request = null) {
			// 1.产生请求对象
			if (null == $request) {
				$request = $this->generateRequest();
				$this->mRequest = $request;
			}
			
			// 2.根据请求，取得路由
			$route = $this->mRouter->getRoute($request);
			list ($moduleAlias, $ctrlClassName, $actionName) = $route;
			$this->mModuleManager->preloadModule($moduleAlias);
			
			// 3.根据配置，创建controller和action并执行
			$preExecutor = Config::Instance()->getModuleConfig($moduleAlias, 'app.executor.pre_executor');
			$dataObj = $route;
			foreach ($preExecutor as $class) {
				$executor = $class::Instance();
				$dataObj = $executor->run($dataObj);
			}
			
			$this->mCurrentController = new $ctrlClassName($moduleAlias);
			$actionMethodName = $actionName;
			
			$e = null;
			try {
				$dataObj = $this->mCurrentController->$actionMethodName($request);
				
				$laterExecutor = Config::Instance()->getModuleConfig($moduleAlias, 'app.executor.later_executor');
				foreach ($laterExecutor as $class) {
					$executor = $class::Instance();
					$dataObj = $executor->run($dataObj);
				}
				
				$response = $this->mViewRender->render($dataObj);
				
				$this->respond($response);
			} catch (\Exception $e) {
			}
			
			$this->operationLog($moduleAlias, $ctrlClassName, $actionName, $this->mCurrentController, $e);
			
			$this->getOutputStream()
				->flush()
				->close();
		}
		
		protected function respond(IResponse $resp){
			
		}

		public function getRequest () {
			return $this->mRequest;
		}

		protected function operationLog ($moduleAlias, $ctrlClassName, $actionName, $controller, $e = null) {
			$enableOperationLog = Config::Instance()->getModuleConfig($moduleAlias, 'app.enableOperationLog');
			if ($enableOperationLog) {
				$isOperation = true;
				if (method_exists($controller, 'isNotOperation')) {
					$isOperation = ! $controller->isNotOperation($actionName);
				}
				
				if ($isOperation) {
					$opResult = null == $e ? true : false;
					$operationName = '';
					if (method_exists($controller, 'getOperationName')) {
						$operationName = $controller->getOperationName($actionName);
					}
					
					$log = $this->getService('log');
					$log->operationLog($moduleAlias, $ctrlClassName, $actionName, $operationName, $opResult, '');
				}
			}
			
			if (null != $e) {
				throw $e;
			}
		}

		/**
		 * 取得唯一实例。
		 *
		 * @return \Framework\App
		 */
		static public function Instance () {
			static $me = null;
			if (null == $me) {
				$me = new App();
			}
			
			return $me;
		}

		public function getVersion () {
			return $this->getConfigValue('version');
		}

		/**
		 * 返回启动的Controller。
		 *
		 * @return \Framework\Controller
		 */
		public function getCurrentController () {
			return $this->mCurrentController;
		}

		public function getController () {
			return $this->mCurrentController;
		}

		protected function generateRequest () {
			if (! array_key_exists('REQUEST_URI', $_SERVER)) {
				return new CliRequest();
			} else {
				return new HttpRequest(true);
			}
		}

		public function getServiceManager () {
			static $sm = null;
			if (null == $sm) {
				$sm = new ServiceManager();
			}
			
			return $sm;
		}

		/**
		 * 对ServiceManager的getService进行包装。
		 * 先检查mServiceManager是否已经设置，如果没有，new一个。再调用其getServer方法。
		 *
		 * @param string $name        	
		 * @param string $caller
		 *        	调用者的模块别名
		 * @return \Framework\IService
		 */
		public function getService ($name, $caller = null) {
			$sm = $this->getServiceManager();
			
			return $sm->getService($name, $caller);
		}

		/**
		 * 根据类名取得模块名
		 * 该函数本来应该放在模块管理类里，但autoload时ModuleManager还没有加载，所以直接放这儿，作为框架的约束：模块名即为类名第一个单词。
		 *
		 * @param string $clsName        	
		 * @throws ParameterErrorException
		 * @return string
		 */
		static public function GetModuleNameByClass ($clsName) {
			$moduleAlias = '';
			$moduleName = '';
			
			$pos = strpos($clsName, '\\');
			if (false !== $pos) {
				$moduleName = substr($clsName, 0, $pos);
				
				return $moduleName;
			} else {
				return null;
				
				// throw new ParameterErrorException(
				// 'can not get module name from class:' . $clsName . '. do not
				// use global namespace.');
			}
		}

		/**
		 * 取得调用者模块
		 *
		 * @return array 第一个值是模块的别名，第二个值是模块名。
		 */
		static public function GetCallerModule () {
			$callerStackInfo = debug_backtrace(~ DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, 4);
			$callerPath = $callerStackInfo[1]['file'];
			// 如果是class_exists这种PHP语言调用时，类文件路径可能在第四个数组元素里，
			// 二三分别被框架的Autoload函数和php语言的spl_autoload_call占据
			if (empty($callerPath)) {
				$callerPath = $callerStackInfo[2]['file'];
			}
			if (empty($callerPath)) {
				$callerPath = $callerStackInfo[3]['file'];
			}
			
			$posStart = strpos($callerPath, App::$ROOT_DIR);
			if (0 == $posStart) {
				$posStart = strlen(App::$ROOT_DIR);
			}
			
			$callerModuleAlias = null;
			$callerModuleName = null;
			$posEnd = strlen($callerPath) - 4; // 去掉.php后缀。
			$moduleDir = substr($callerPath, $posStart, $posEnd - $posStart);
			
			$mm = ModuleManager::Instance();
			// 本来想通过debug_backtrace返回的类名，直接找到模块所在的dir，但有的类的名字空间与文件不一定一致，比如App\ClassLoader
			while (true) {
				$posEnd = strrpos($moduleDir, DIRECTORY_SEPARATOR, - 2); // -2表示从倒数第二个字符开始找
				if (false === $posEnd) { // 不可能发生。
					break;
				}
				
				$moduleDir = substr($moduleDir, 0, $posEnd + 1);
				list ($callerModuleAlias, $moduleConf) = $mm->getLoadedModuleAliasByPath($moduleDir);
				if ('framework' == $callerModuleAlias || 'HFC' == $callerModuleAlias) {
					$callerModuleName = $callerModuleAlias;
					
					break;
				} else if (! empty($callerModuleAlias)) {
					$callerModuleName = $moduleConf['name'];
					
					break;
				}
			}
			
			return array(
				$callerModuleAlias,
				$callerModuleName
			);
		}

		public function useModule ($alias) {
			$this->mClassLoader->useModule($alias);
		}

		public function setOutputStream (IOutputStream $s) {
			$this->mOutputStream = $s;
		}

		public function getOutputStream () {
			if (null == $this->mOutputStream) {
				$this->mOutputStream = new StandardOutputStream();
			}
			
			return $this->mOutputStream;
		}
	}
	
	App::$ROOT_DIR = dirname(__DIR__) . DIRECTORY_SEPARATOR;
}

namespace Framework\App {

	use Framework\App;
	use Framework\Module\ModuleManager;

	/**
	 * 根据名字空间，include类的定义。此类的目的就是封装include函数。
	 * PHP没有内部类的语法，把代码写在一个文件里解决。
	 *
	 * @author Hoheart
	 *        
	 */
	class ClassLoader {
		
		/**
		 * 为了快速访问
		 *
		 * @var string
		 */
		public static $FRAMEWORK_DIR;
		public static $HFC_DIR;
		public static $COMMON_MODULE_DIR;
		
		/**
		 * 当出现多个模块同名时，用这个。
		 *
		 * @var string
		 */
		protected $mUsedModule = '';

		public function __construct () {
		}

		/**
		 * 调用spl_autoload_register ( array ($this,'autoload'), true,true
		 * );注册给PHP解释器。
		 */
		public function register2System () {
			spl_autoload_register(array(
				$this,
				'autoload'
			));
		}

		/**
		 * 根据类名对应的路径，装载类。
		 *
		 * @param string $className        	
		 */
		public function autoload ($className) {
			$moduleDir = null;
			
			$moduleName = App::GetModuleNameByClass($className);
			if (null == $moduleName) {
				return;
			}
			
			// 任何模块都可以调用Framework和HFC
			if ('Framework' == $moduleName) {
				$moduleDir = self::$FRAMEWORK_DIR;
			} else if ('HFC' == $moduleName) {
				$moduleDir = self::$HFC_DIR;
			} else if ('Common' == $moduleName) {
				$moduleDir = self::$COMMON_MODULE_DIR;
			} else {
				// 只允许访问调用者自己的模块
				list ($callerAlias, $callerName) = App::GetCallerModule();
				// 如果调用者不是Framework和HFC，只能是自己调用自己
				// 一个模块调用另一个模块的服务，只能通过Module对象提供的服务调用。
				if ('framework' != $callerAlias && 'hfc' != $callerAlias && $callerName != $moduleName) {
					return null;
				}
				
				// 根据调用的类名，取得调用的是哪个模块
				$alias = '';
				$aliasMap = ModuleManager::Instance()->getLoadedModuleAliasByName($moduleName);
				if (1 == count($aliasMap)) {
					$alias = key($aliasMap);
				} else {
					$alias = $this->mUsedModule;
				}
				$moduleDir = ModuleManager::Instance()->getModulePath($alias);
			}
			
			$pos = strpos($className, '\\');
			$relativeClassName = substr($className, $pos + 1);
			$path = $moduleDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClassName) . '.php';
			
			return $this->loadFile($path);
		}

		public function useModule ($alias) {
			$this->mUsedModule = $alias;
		}

		/**
		 * 把一个file include进来
		 *
		 * @param string $path
		 *        	可以是绝对路径，也可以是app根目录的相对路径。
		 */
		public function loadFile ($path) {
			include_once App::$ROOT_DIR . $path; // 如果用require，就会产生一个系统错误，而用户自定义错误就截取不到了。
		}
	}
	
	ClassLoader::$FRAMEWORK_DIR = 'Framework' . DIRECTORY_SEPARATOR;
	ClassLoader::$HFC_DIR = ClassLoader::$FRAMEWORK_DIR . 'HFC' . DIRECTORY_SEPARATOR;
	ClassLoader::$COMMON_MODULE_DIR = 'Common' . DIRECTORY_SEPARATOR;
}