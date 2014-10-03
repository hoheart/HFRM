<?php

namespace hhp {

	use hhp\app\ClassLoader;
	use hfc\event\EventManager;
	use hfc\event\IEvent;
	use hfc\util\Util;
	use hhp\exception\RequestException;
	use hfc\exception\ParameterErrorException;

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
		 * 应用程序配置。
		 *
		 * @var map
		 */
		protected $mAppConf = null;
		
		/**
		 * 本来ServiceManager也是一个IServer实例，但还没ServiceManager实例时，只能是放这儿了，
		 * 其他的可以通过ServiceManager来保存。
		 *
		 * @var \Hhp\ServiceManager
		 */
		protected $mServiceManager = null;
		
		/**
		 * 启动模块的配置文件，即当前访问的index.php所在的模块。
		 *
		 * @var map
		 */
		protected $mBootModuleConf = null;
		
		/**
		 * 存放ClassLoader。
		 *
		 * @var \Hhp\App\ClassLoader;
		 */
		protected $mClassLoader = null;
		
		/**
		 * 启动的Controller，即url中指定要访问的Controller。
		 *
		 * @var \Hhp\Controller
		 */
		protected $mBootController = null;

		/**
		 * 构造函数，创建ClassLoader，并调用其register2System。
		 */
		protected function __construct () {
			// 切换到App目录。
			chdir(self::getRootDir());
			
			$this->mClassLoader = new ClassLoader();
			$this->mClassLoader->register2System();
			
			$errorHandler = new ErrorHandler();
			$errorHandler->register2System();
		}

		/**
		 * 取得唯一实例。
		 *
		 * @return \Hhp\App
		 */
		static public function Instance () {
			static $me = null;
			if (null == $me) {
				$me = new App();
			}
			
			return $me;
		}

		/**
		 * 根据run传入的配置，取得default_controller配置，确定controller和action后，
		 * 再调用controller的getConfig，最后合并配置，并赋值给成员变量。
		 *
		 * @param \Hhp\IRequest $reqeust
		 *        	当前的请求
		 * @param array $bootModuleConf
		 *        	启动模块的配置
		 * @param array $defaultControllerConf
		 *        	默认controller的配置
		 * @param ClassLoader $clsLoader
		 *        	ClassLoader
		 *        	
		 * @return Controller
		 */
		protected function createController (IRequest $request, array $bootModuleConf, 
				array $defaultControllerConf, ClassLoader $clsLoader) {
			$c = $request->getVal('c');
			if (empty($c)) {
				$c = $defaultControllerConf['controller'];
			}
			
			$ctrlClass = $clsLoader->loadController($moduleConf, $c, $bootModuleConf);
			$ctrl = new $ctrlClass();
			
			$ctrl->setRequest($request);
			
			return $ctrl;
		}

		public function getActionName (IRequest $request, array $defaultControllerConf) {
			$c = $request->getVal('c');
			$a = $request->getVal('a');
			if (empty($c)) {
				$a = $defaultControllerConf['action'];
			} else if (empty($a)) {
				$a = $defaultControllerConf['action'];
			}
			
			return $a;
		}

		public function getVersion () {
			return $this->getConfigValue('version');
		}

		public function getController () {
			return $this->mBootController;
		}

		/**
		 * 根据请求，产生IRequest对象。
		 *
		 * @return \Hhp\IRequest
		 */
		protected function generateRequest () {
			if (! empty($_SERVER['HTTP_HOST'])) {
				$r = new HttpRequest();
				$r->setBody($_REQUEST);
				
				return $r;
			}
			
			return null;
		}

		/**
		 * 启动应用程序。
		 *
		 * @param array $conf
		 *        	模块的配置。
		 */
		public function run () {
			// 1.取得系统配置文件
			$this->mAppConf = $this->loadConfigFile('config' . DIRECTORY_SEPARATOR . 'Config.php');
			
			// 2.根据请求，取得请求模块的配置文件。
			$request = $this->generateRequest();
			
			$moduleConf = $this->getModuleConf($request);
			
			// 3.根据配置，创建controller
			$bootModule = $this->getConfigValue('boot_module');
			$moduleConf = $this->getConfigValue('module');
			$bootModuleConf = $moduleConf[$bootModule];
			$defaultControllerConf = $this->getConfigValue('default_controller');
			$this->mBootController = $this->createController($request, $bootModuleConf, 
					$defaultControllerConf, $this->mClassLoader);
			
			// 4.取得controller之后，合并新的配置
			$actionName = $this->getActionName($request, $defaultControllerConf);
			$this->mBootModuleConf = $this->combinActionConf($this->mBootController, 
					$this->mBootModuleConf, $actionName);
			
			// 5.这时，就可以根据配置，进行路由了
			$route = $this->getRoute($request);
			$this->route($route);
		}

		protected function combinActionConf (Controller $ctrl, $oldConf, $action) {
			$actionConf = $ctrl->getConfig($action);
			return Util::mergeArray($oldConf, $actionConf);
		}

		/**
		 * 取得完成这次请求的路由。包括pre_executer、
		 * Controller对象（注意是对象，之前已经调用过createController创建了控制器了）、
		 * Action名称和later_executer。
		 *
		 * @return array 参见route方法。
		 */
		public function getRoute () {
			$route = $this->getConfigValue('route');
			$route['controller'] = $this->mBootController;
			
			$r = $this->mBootController->getRequest();
			$dcc = $this->getConfigValue('default_controller');
			$route['action'] = $this->getActionName($r, $dcc);
			
			return $route;
		}

		/**
		 * 执行getRoute返回的路由协议。
		 *
		 * @param array $route
		 *        	路由协议。
		 *        	array(
		 *        	'pre_executer'=> array(
		 *        	'namespace\className' ,
		 *        	'namespace1\className1'
		 *        	),
		 *        	'later_executer'=>array(
		 *        	'namespace\className' ,
		 *        	'namespace1\className1'
		 *        	),
		 *        	'controller'=>Controller,
		 *        	'action'=>'actionName'
		 *        	)
		 */
		public function route (array $route) {
			$dataObj = null;
			foreach ($route['pre_executer'] as $class) {
				$executer = $class::Instance();
				$dataObj = $executer->run($dataObj);
			}
			
			$a = $route['action'];
			$view = $route['controller']->$a($dataObj);
			if (! empty($view)) {
				$dataObj['controllerReturnView'] = $view;
			}
			
			foreach ($route['later_executer'] as $class) {
				$executer = $class::Instance();
				$dataObj = $executer->run($dataObj);
			}
		}

		/**
		 * 对ServiceManager的getService进行包装。
		 * 先检查mServiceManager是否已经设置，如果没有，new一个。再调用其getServer方法。
		 *
		 * @param string $name        	
		 * @return \Hhp\IService
		 */
		public function getService ($name) {
			if (null == $this->mServiceManager) {
				$conf = $this->getConfigValue('service');
				$this->mServiceManager = new ServiceManager($conf);
			}
			
			return $this->mServiceManager->getService($name);
		}

		/**
		 * 对EventManager的trigger的包装。
		 *
		 * @param object $event        	
		 * @param object $sender        	
		 * @param object $dataObj        	
		 */
		public function trigger ($event, $sender = null, $dataObject = null) {
			$eventManager = $this->getService('EventManager');
			if ($event instanceof IEvent) {
				$eventManager->trigger($event);
			} else if (is_string($event)) {
				$eventManager->triggerCommonEvent($event, $sender, $dataObject);
			} else {
				throw new ParameterErrorException(
						'the $event parameter of App::trigger() must be a string or IEvent');
			}
		}

		/**
		 * 返回启动的Controller。
		 *
		 * @return \Hhp\Controller
		 */
		public function getCurrentController () {
			return $this->mBootController;
		}

		/**
		 * 封装的ClassLoader的函数。
		 *
		 * @param string $path        	
		 */
		public function loadConfigFile ($path) {
			return $this->mClassLoader->loadFile($path);
		}

		/**
		 * 先取得模块的配置，如果没有，再从app的配置里取得。
		 *
		 * @param string $name        	
		 */
		public function getConfigValue ($key) {
			if (key_exists($key, $this->mBootModuleConf)) {
				return $this->mBootModuleConf[$key];
			} else {
				return $this->mAppConf[$key];
			}
		}
	}
	
	App::$ROOT_DIR = dirname(__DIR__) . DIRECTORY_SEPARATOR;
}

namespace hhp\App {

	use hhp\exception\ModuleNotEnableException;
	use hhp\exception\ConfigErrorException;
	use hhp\App;

	/**
	 * 根据名字空间，include类的定义。此类的目的就是封装include函数。
	 * PHP没有内部类的语法，把代码写在一个文件里解决。
	 *
	 * @author Hoheart
	 *        
	 */
	class ClassLoader {

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
		 * 要么load hhp或hfc中的类，要么load 执行代码所在模块和该模块引用模块的类。
		 *
		 * 用debug_backtrace(
		 * ~ DEBUG_BACKTRACE_PROVIDE_OBJECT |
		 * DEBUG_BACKTRACE_IGNORE_ARGS)取得调用栈数组，数组的第一个元素就是new类的地方：[1] => Array
		 * (
		 * [file] => E:\web\test.php
		 * [line] => 19
		 * [function] => spl_autoload_call
		 * )，根据file找到对应的模块(注意模块可能嵌套在别的文件夹)。
		 * 再解析class找到模块名，再在上面模块中对应的引用模块中找类对应的文件。
		 *
		 * @param string $className        	
		 * @throws \Exception
		 */
		public function autoload ($className) {
			// 确定要载入的类所在的模块别名。（要引用别的模块，用模块别名打头）
			list ($moduleName, $relativeClassName) = $this->getClassModule($className);
			
			$moduleDir = null;
			if ('hhp' == $moduleName) {
				$moduleDir = '';
			} else if ('hfc' == $moduleName) {
				$moduleDir = 'hfc' . DIRECTORY_SEPARATOR;
			} else {
				// 确定当前运行的模块。根据一个文件只能在一个模块存在（其绝对路径只能属于一个模块）的原则。
				$app = App::Instance();
				$moduleDirIndex = $app->getConfigValue('module_dir_index');
				$moduleConfArr = $app->getConfigValue('module');
				
				$stack = debug_backtrace(
						~ DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS);
				$callPath = $stack[1]['file'];
				$callModule = $this->getCallModule($callPath, $moduleDirIndex);
				if ('hhp' == $callModule) { // Hhp模块可以调用任何代码，所以，一旦是Hhp调用，就相当于模块自己调用自己
					$callModule = $moduleName;
				}
				
				$callModuleConf = $moduleConfArr[$callModule];
				if (null == $callModuleConf) {
					throw new ConfigErrorException('can not find self module: ' . $moduleName);
				}
				
				// 说明是跟调用者在同一模块。
				if ($moduleName == $callModuleConf['name']) {
					$this->checkModuleConf($callModuleConf);
					$moduleDir = $callModuleConf['dir'];
				} else { // 在引用模块里
					if (! in_array($moduleName, $callModuleConf['dependence'])) {
						return;
					}
					
					$dependenceModuleConf = $moduleConfArr[$moduleName];
					$this->checkModuleConf($dependenceModuleConf);
					$moduleDir = $dependenceModuleConf['dir'];
				}
			}
			
			return $this->includeFile($moduleDir, $relativeClassName);
		}

		protected function includeFile ($dir, $relativeName) {
			$path = $dir . $relativeName . '.php';
			return $this->loadFile($path);
		}

		public function loadFile ($path) {
			return include $path;
		}

		protected function getClassModule ($className) {
			$pos = strpos($className, '\\');
			return array(
				substr($className, 0, $pos),
				substr($className, $pos + 1)
			);
		}

		/**
		 * 取得要调用的模块。
		 * 根据调用代码所在位置和各模块所在位置，确定调用代码所在位置。
		 *
		 * @param string $callFilePath        	
		 * @param array $moduleDirIndex        	
		 * @return string NULL
		 */
		protected function getCallModule ($callFilePath, array $moduleDirIndex) {
			// Hhp模块默认可以调用任何模块的代码，所以，要判断是否是Hhp在调用。
			static $hhpDir = null;
			if (null == $hhpDir) {
				$hhpDir = App::getRootDir() . 'Hhp' . DIRECTORY_SEPARATOR;
			}
			
			$parentDir = $callFilePath;
			do {
				$callModuleDir = $parentDir;
				$moduleAlias = $moduleDirIndex[$callModuleDir];
				if (null != $moduleAlias) {
					return $moduleAlias;
				} else if ($hhpDir == $callModuleDir) {
					return 'Hhp';
				}
				$parentDir = dirname($callModuleDir) . DIRECTORY_SEPARATOR;
			} while ($callModuleDir != $parentDir);
			
			return null;
		}

		protected function checkModuleConf ($moduleConf) {
			if (! $moduleConf['enable']) {
				throw new ModuleNotEnableException(
						'Module: ' . $moduleConf['name'] . ' not enabled.');
			}
		}

		public function loadController ($moduleConf, $controllerName, array $bootModuleConf) {
			$bootModuleDir = $bootModuleConf['dir'];
			$ctrlDir = $bootModuleConf['controller_dir'];
			
			$this->includeFile($bootModuleDir . $ctrlDir, $controllerName);
			
			return $bootModuleConf['name'] . '\\' . $ctrlDir . $controllerName;
		}
	}
}