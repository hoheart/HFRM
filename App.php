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
	use Framework\Output\StandardOutputStream;
	use Framework\Output\IOutputStream;
	use Framework\Response\IResponse;
	use Framework\Response\HttpResponse;
	use Framework\Exception\EndAppException;

	/**
	 * 框架的核心就三个组件：
	 * ClassLoader：保证模块之间的隔离；
	 * ModuleManager：提供模块管理功能和接口调用；
	 * ServiceManager：提供像数据库等常用的服务，可以通过配置文件任意指定。
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
		public static $ROOT_DIR = '';
		
		/**
		 * 存放ClassLoader。
		 *
		 * @var ClassLoader
		 */
		protected $mClassLoader = null;
		
		/**
		 *
		 * @var ErrorHandler
		 */
		protected $mErrorHandler = null;
		
		/**
		 *
		 * @var ModuleManager
		 */
		protected $mModuleManager = null;
		
		/**
		 *
		 * @var ServiceManager
		 */
		protected $mServiceManager = null;
		
		/**
		 * 以上的成员变量都是框架所需的三个组件，下面的就是这次请求有关的组件，一般是可以通过配置或根据请求参数更改的。
		 *
		 * @var IRequest
		 */
		protected $mRequest = null;
		
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
		 *
		 * @var IResponse
		 */
		protected $mResponse = null;

		/**
		 * 构造函数，创建ClassLoader，并调用其register2System。
		 */
		protected function __construct () {
			// 切换到App目录。
			chdir(self::$ROOT_DIR);
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

		/**
		 * 单独提出来，可能有的程序只想用自动加载器
		 */
		public function registerAutoloader () {
			$this->mClassLoader = new ClassLoader();
			$this->mClassLoader->register2System();
		}

		public function init () {
			$this->registerAutoloader();
			
			$this->mErrorHandler = new ErrorHandler();
			$this->mErrorHandler->register2System();
			
			// 还没想好怎么处理，暂时放这儿。
			RequestFilter::RemoveSQLInjection();
			
			// 还没想好怎么处理，暂时放这儿。
			date_default_timezone_set(Config::Instance()->get('app.localTimezone'));
			
			$this->mModuleManager = ModuleManager::Instance();
			
			$routerCls = Config::Instance()->get('app.router');
			if (empty($routerCls)) {
				$routerCls = '\Framework\Router\PathParseRouter';
			}
			$this->mRouter = $routerCls::Instance();
			
			$this->mViewRender = new ViewRender();
		}

		public function start () {
			if (null != $this->mModuleManager) {
				$this->mModuleManager->start();
			}
			
			if (null != $this->mServiceManager) {
				$this->mServiceManager->start();
			}
		}

		public function stop () {
			if (null != $this->mServiceManager) {
				$this->mServiceManager->stop();
			}
			
			if (null != $this->mModuleManager) {
				$this->mModuleManager->stop();
			}
		}

		/**
		 * 运行应用程序
		 *
		 * @param IRequest $request
		 *        	主要给swoole用的。如果是swoole，考虑到重入问题，不能用全局变量，在外面创建号request传进来
		 */
		public function run (IRequest $request = null) {
			// 1.产生请求对象
			if (null == $request) {
				$request = $this->generateRequest();
			}
			$this->mRequest = $request;
			
			// 防止直接echo等的输出，避免xss攻击。
			ob_start();
			
			// 2.根据请求，取得路由
			try {
				$this->start();
				
				$route = $this->mRouter->getRoute($request);
				list ($moduleAlias, $ctrlClassName, $actionMethodName) = $route;
				
				// 3.根据配置，创建controller和action并执行
				$preExecutor = Config::Instance()->getModuleConfig($moduleAlias, 'app.executor.pre_executor');
				$dataObj = $route;
				foreach ($preExecutor as $class) {
					$executor = $class::Instance();
					$dataObj = $executor->run($dataObj);
				}
				
				$this->mCurrentController = new $ctrlClassName($moduleAlias);
				$dataObj = $this->mCurrentController->$actionMethodName($request);
				
				$laterExecutor = Config::Instance()->getModuleConfig($moduleAlias, 'app.executor.later_executor');
				foreach ($laterExecutor as $class) {
					$executor = $class::Instance();
					$dataObj = $executor->run($dataObj);
				}
				
				$response = $this->mViewRender->render($dataObj);
				
				// 输出剩余内容,放在stop前，让浏览器更快看到页面。
				$this->respond($response);
				
				$this->stop();
			} catch (\Exception $e) {
				$this->mErrorHandler->handleException($e);
			}
			
			$this->operationLog($moduleAlias, $ctrlClassName, $actionMethodName, $this->mCurrentController, $e);
			
			return null === $e ? true : false;
		}

		public function respond (IResponse $resp = null) {
			if (null == $resp) {
				$resp = $this->getResponse();
			}
			
			$output = $this->getOutputStream();
			
			$output->output($resp);
			// 当此输出完后，清空，以备下次输出。
			$resp->clear();
			
			$output->flush();
			$output->close();
		}

		public function getRequest () {
			return $this->mRequest;
		}

		public function getResponse () {
			if (null == $this->mResponse) {
				$this->mResponse = new HttpResponse();
				
				$stream = $this->getOutputStream();
				$this->mResponse->setOutputStream($stream);
			}
			
			return $this->mResponse;
		}

		protected function operationLog ($moduleAlias, $ctrlClassName, $actionName, $controller, $e = null) {
			// EndAppException相当于调用exit，不是错误
			if ($e instanceof EndAppException) {
				$e = null;
			}
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
			if ('cli' == PHP_SAPI) {
				return new CliRequest();
			} else {
				return new HttpRequest(true);
			}
		}

		public function getServiceManager () {
			if (null == $this->mServiceManager) {
				$this->mServiceManager = new ServiceManager();
			}
			
			return $this->mServiceManager;
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

		/**
		 * 直接结束程序运行。相当于调用exit。
		 * 在swoole框架里，调用exit会引起所有资源回收，导致连接池破损。
		 *
		 * @throws EndAppException
		 */
		static public function end () {
			throw new EndAppException();
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
			static $bRegister = false;
			if (! $bRegister) {
				spl_autoload_register(array(
					$this,
					'autoload'
				));
				
				$bRegister = true;
			}
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