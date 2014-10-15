<?php

namespace hhp {

	use hhp\app\ClassLoader;
	use hfc\event\IEvent;
	use hfc\util\Util;
	use hfc\exception\ParameterErrorException;
	use hhp\ErrorHandler;

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
		 * 启动模块的配置文件，即当前访问的controller所在的模块的配置。
		 *
		 * @var map
		 */
		protected $mBootModuleConf = null;
		
		/**
		 * 本来ServiceManager也是一个IServer实例，但还没ServiceManager实例时，只能是放这儿了，
		 * 其他的可以通过ServiceManager来保存。
		 *
		 * @var \hhp\ServiceManager
		 */
		protected $mServiceManager = null;
		
		/**
		 * 存放ClassLoader。
		 *
		 * @var \hhp\App\ClassLoader;
		 */
		protected $mClassLoader = null;
		
		/**
		 * 启动的Controller，即url中指定要访问的Controller。
		 *
		 * @var \hhp\Controller
		 */
		protected $mBootController = null;
		
		/**
		 * 根据请求的url，取得模块名、controller名、action。
		 *
		 * @var array
		 */
		protected $mRedirection = null;
		
		/**
		 * 模块配置文件Map。
		 *
		 * @var array
		 */
		protected $mModuleConfMap = array ();
		
		/**
		 * 构造函数，创建ClassLoader，并调用其register2System。
		 */
		protected function __construct() {
			// 切换到App目录。
			chdir ( self::$ROOT_DIR );
			
			$this->mClassLoader = new ClassLoader ();
			$this->mClassLoader->register2System ();
			
			$errorHandler = new ErrorHandler ();
			$errorHandler->register2System ();
		}
		
		/**
		 * 取得唯一实例。
		 *
		 * @return \hhp\App
		 */
		static public function Instance() {
			static $me = null;
			if (null == $me) {
				$me = new App ();
			}
			
			return $me;
		}
		public function getVersion() {
			return $this->getConfigValue ( 'version' );
		}
		
		/**
		 * 返回启动的Controller。
		 *
		 * @return \hhp\Controller
		 */
		public function getCurrentController() {
			return $this->mBootController;
		}
		public function getController() {
			return $this->mBootController;
		}
		
		/**
		 * 根据请求，产生IRequest对象。
		 *
		 * @return \hhp\IRequest
		 */
		public function getRequest() {
			static $r = null;
			if (null != $r) {
				return $r;
			}
			
			if (! empty ( $_SERVER ['HTTP_HOST'] )) {
				$r = new HttpRequest ();
				$r->setBody ( $_REQUEST );
				
				return $r;
			}
			
			return $r;
		}
		
		/**
		 * 启动应用程序。
		 *
		 * @param array $conf
		 *        	模块的配置。
		 */
		public function run() {
			// 1.取得系统配置文件
			$this->mAppConf = $this->mClassLoader->loadFile ( 'config' . DIRECTORY_SEPARATOR . 'Config.php' );
			
			// 2.根据请求，取得请求模块的配置文件。
			list ( $moduleAlias, $ctrlName, $actionName ) = $this->getRedirection ();
			if (empty ( $moduleAlias )) {
				$moduleAlias = $this->mAppConf ['default_module'];
			}
			$moduleConf = $this->getModuleConf ( $moduleAlias );
			$this->mBootModuleConf = Util::mergeArray ( $this->mAppConf, $moduleConf );
			
			// 3.根据配置，加载controller类，合并新的配置
			if (empty ( $ctrlName )) {
				$ctrlName = $this->mBootModuleConf ['default_controller'] ['controller_name'];
				$actionName = $this->mBootModuleConf ['default_controller'] ['action_name'];
			}
			if (empty ( $actionName )) {
				$actionName = $this->mBootModuleConf ['default_controller'] ['action_name'];
			}
			$appConfModule = $this->mAppConf ['module'] [$moduleAlias];
			$moduleName = $appConfModule ['name'];
			$ctrlDir = $this->mBootModuleConf ['controller_dir'];
			
			$ctrlClassRelativeName = ucfirst ( $ctrlName ) . 'Controller';
			$ctrlClassName = $moduleName . '\\' . str_replace ( '/', '\\', $ctrlDir ) . $ctrlClassRelativeName;
			
			$this->mClassLoader->loadController ( $moduleAlias, $ctrlClassRelativeName, $ctrlClassName );
			
			$actionMethodName = $actionName . 'Action';
			$this->mBootModuleConf = $this->combinActionConf ( $ctrlClassName, $this->mBootModuleConf, $actionName );
			
			// 4.这时，就可以根据配置，进行路由了
			$executer = $this->getConfigValue ( 'executor' );
			$dataObj = null;
			foreach ( $executer ['pre_executor'] as $class ) {
				$executer = $class::Instance ();
				$dataObj = $executer->run ( $dataObj );
			}
			
			$this->mBootController = new $ctrlClassName ();
			$this->mBootController->$actionMethodName ( $_REQUEST );
			
			foreach ( $executer ['later_executer'] as $class ) {
				$executer = $class::Instance ();
				$dataObj = $executer->run ( $dataObj );
			}
		}
		
		/**
		 * 根据请求的url，取得重定向相关信息。
		 *
		 * @return array
		 */
		protected function getRedirection() {
			if (! empty ( $this->mRedirection )) {
				return $this->mRedirection;
			}
			
			$uri = $_SERVER ['REQUEST_URI'];
			$uriLen = strlen ( $uri );
			
			$actionName = '';
			$pos = strrpos ( $uri, '?' );
			if ($pos > 0) {
				$pos -= 1;
			} else {
				$pos = $uriLen - 1;
			}
			$pos1 = strrpos ( $uri, '/', $pos - $uriLen );
			$actionName = substr ( $uri, $pos1 + 1, $pos - $pos1 );
			
			$ctrlName = '';
			while ( $pos1 > 0 ) {
				$pos = $pos1 - 1;
				$pos1 = strrpos ( $uri, '/', $pos - $uriLen );
				$ctrlName = substr ( $uri, $pos1 + 1, $pos - $pos1 );
				if (empty ( $ctrlName )) {
					continue;
				}
				
				break;
			}
			
			$moduleAlias = substr ( $uri, 1, $pos1 - $uriLen );
			
			$this->mRedirection = array (
					$moduleAlias,
					$ctrlName,
					$actionName 
			);
			
			return $this->mRedirection;
		}
		protected function combinActionConf($ctrlClassName, $oldConf, $action) {
			$actionConf = $ctrlClassName::getConfig ( $action );
			return Util::mergeArray ( $oldConf, $actionConf );
		}
		
		/**
		 * 对ServiceManager的getService进行包装。
		 * 先检查mServiceManager是否已经设置，如果没有，new一个。再调用其getServer方法。
		 *
		 * @param string $name        	
		 * @return \hhp\IService
		 */
		public function getService($name) {
			if (null == $this->mServiceManager) {
				$conf = $this->getConfigValue ( 'service' );
				$this->mServiceManager = new ServiceManager ( $conf );
			}
			
			return $this->mServiceManager->getService ( $name );
		}
		
		/**
		 * 对EventManager的trigger的包装。
		 *
		 * @param object $event        	
		 * @param object $sender        	
		 * @param object $dataObj        	
		 */
		public function trigger($event, $sender = null, $dataObject = null) {
			$eventManager = $this->getService ( 'EventManager' );
			if ($event instanceof IEvent) {
				$eventManager->trigger ( $event );
			} else if (is_string ( $event )) {
				$eventManager->triggerCommonEvent ( $event, $sender, $dataObject );
			} else {
				throw new ParameterErrorException ( 'the $event parameter of App::trigger() must be a string or IEvent' );
			}
		}
		
		/**
		 * 封装的ClassLoader的函数。
		 *
		 * @param string $moduleAlias        	
		 */
		public function getModuleConf($moduleAlias) {
			$conf = $this->mModuleConfMap [$moduleAlias];
			if (empty ( $conf )) {
				$appConfigModuleArr = $this->getConfigValue ( 'module' );
				$appConfigModule = $appConfigModuleArr [$moduleAlias];
				$configFilePath = $appConfigModule ['dir'] . 'config' . DIRECTORY_SEPARATOR . 'config.php';
				$conf = $this->mClassLoader->loadFile ( $configFilePath );
				$this->mModuleConfMap [$moduleAlias] = $conf;
			}
			
			return $conf;
		}
		
		/**
		 * 先取得模块的配置，如果没有，再从app的配置里取得。
		 *
		 * @param string $name        	
		 */
		public function getConfigValue($key) {
			$conf = $this->mBootModuleConf;
			if (empty ( $conf )) {
				$conf = $this->mAppConf;
			}
			return $conf [$key];
		}
	}
	
	App::$ROOT_DIR = dirname ( __DIR__ ) . DIRECTORY_SEPARATOR;
}

namespace hhp\App {

	use hhp\exception\ModuleNotEnableException;
	use hhp\App;
	use hhp\exception\APINotAvailableException;
	use hhp\exception\ConfigErrorException;
	use hhp\exception\RequestErrorException;

	/**
	 * 根据名字空间，include类的定义。此类的目的就是封装include函数。
	 * PHP没有内部类的语法，把代码写在一个文件里解决。
	 *
	 * @author Hoheart
	 *        
	 */
	class ClassLoader {
		public static $HHP_DIR;
		public static $HFC_DIR;
		
		/**
		 * 记录模块路径与模块的对应关系。每调用一次记录一次。
		 *
		 * @var array
		 */
		private $mModuleDirIndex = array ();
		
		/**
		 * 调用spl_autoload_register ( array ($this,'autoload'), true,true
		 * );注册给PHP解释器。
		 */
		public function register2System() {
			spl_autoload_register ( array (
					$this,
					'autoload' 
			) );
		}
		
		/**
		 * 根据类名对应的路径，装在类。
		 *
		 * @param string $className        	
		 */
		public function autoload($className) {
			// 确定要载入的类所在的模块别名。（要引用别的模块，用模块别名打头）
			list ( $moduleAlias, $relativeClassName ) = $this->getClassModule ( $className );
			
			$moduleDir = null;
			// 任何模块都可以调用hhp和hfc
			if ('hhp' == $moduleAlias) {
				$moduleDir = self::$HHP_DIR;
			} else if ('hfc' == $moduleAlias) {
				$moduleDir = self::$HFC_DIR;
			} else {
				list ( $callerAlias, $callerName ) = $this->getCallerModule ();
				
				$app = App::Instance ();
				$appConfModuleArr = $app->getConfigValue ( 'module' );
				$appConfModule = $appConfModuleArr [$moduleAlias];
				
				// 优先处理自己模块的调用关系。
				// hhp和hfc可以调用任何模块
				if ($callerName == $moduleAlias || 'hhp' == $callerAlias || 'hfc' == $callerAlias) {
					// 是自己调用自己，就取调用者的模块路径。
					$moduleDir = $appConfModule ['dir'];
				} else {
					$callerModuleConf = $app->getModuleConf ( $callerAlias );
					$moduleConf = $app->getModuleConf ( $moduleAlias );
					$this->checkModuleConf ( $appConfModule, $callerModuleConf, $moduleConf, $moduleAlias, $relativeClassName );
					
					$moduleDir = $appConfModule ['dir'];
					
					if (! key_exists ( $moduleDir, $this->mModuleDirIndex )) {
						$this->mModuleDirIndex [$moduleDir] = $moduleAlias;
					}
				}
			}
			
			$path = $moduleDir . str_replace ( '\\', '/', $relativeClassName ) . '.php';
			
			return $this->loadFile ( $path );
		}
		
		/**
		 * 取得调用者模块
		 *
		 * @return array 第一个值是模块的别名，第二个值是模块名。
		 */
		protected function getCallerModule() {
			$callerInfo = debug_backtrace ( ~ DEBUG_BACKTRACE_PROVIDE_OBJECT | ~ DEBUG_BACKTRACE_IGNORE_ARGS, 4 )[3];
			$callerPath = $callerInfo ['file'];
			$callerClass = $callerInfo ['class'];
			list ( $callerModuleName, $relativeClassName ) = $this->getClassModule ( $callerClass );
			if ('hhp' == $callerModuleName || 'hfc' == $callerModuleName) {
				return array (
						$callerModuleName,
						$callerModuleName 
				);
			}
			
			$relativePath = $relativeClassName;
			if (DIRECTORY_SEPARATOR != '\\') {
				$relativePath = str_replace ( '\\', DIRECTORY_SEPARATOR, $relativePath );
			}
			$posStart = strpos ( $callerPath, App::$ROOT_DIR );
			if (0 == $posStart) {
				$posStart = strlen ( App::$ROOT_DIR );
			}
			$posEnd = strrpos ( $callerPath, $relativePath, - 4 );
			
			$moduleDir = substr ( $callerPath, $posStart, $posEnd - $posStart );
			
			return array (
					$this->mModuleDirIndex [$moduleDir],
					$callerModuleName 
			);
		}
		
		/**
		 * 检查被调用的模块的相关配置是否正确
		 *
		 * @param array $appConfModule
		 *        	app module配置里该模块的配置
		 * @param array $callerModuleConf
		 *        	调用者模块的配置
		 * @param array $moduleConfig
		 *        	被调用模块的配置
		 * @param string $moduleAlias
		 *        	被调用模块的别名
		 * @param string $relativeClassName
		 *        	除掉模块名，剩下的类名
		 *        	
		 * @throws ModuleNotEnableException
		 * @throws ConfigErrorException
		 * @throws RequestErrorException
		 * @throws APINotAvailableException
		 */
		protected function checkModuleConf($appConfModule, $callerModuleConf, $moduleConfig, $moduleAlias, $relativeClassName) {
			// 1.检查被调用模块有没有被开启
			if (! $appConfModule ['enable']) {
				throw new ModuleNotEnableException ( "Module: {$appConfModule['name']} not enabled." );
			}
			
			// 2.检查调用者有没有依赖这个模块
			if (! key_exists ( $moduleAlias, $callerModuleConf ['depends'] )) {
				throw new ConfigErrorException ( "no depends on module: $moduleAlias" );
			}
			
			// 3.检查模块有没有提供这个接口
			$className = $appConfModule ['name'] . '\\' . $relativeClassName;
			if (! $moduleConfig ['API'] [$className] ['enable']) {
				throw new APINotAvailableException ( "API : $className is not available." );
			}
		}
		public function loadController($moduleAlias, $relativeControllerName, $controllerName) {
			$app = App::Instance ();
			$appConfModule = $app->getConfigValue ( 'module' )[$moduleAlias];
			$moduleConf = $app->getModuleConf ( $moduleAlias );
			
			$controllerConf = $moduleConf ['controller'] [$controllerName];
			if (! empty ( $controllerConf )) {
				if ($controllerConf ['enable']) {
					$path = $appConfModule ['dir'] . $moduleConf ['controller_dir'] . $relativeControllerName . '.php';
					
					return $this->loadFile ( $path );
				}
			}
			
			throw new RequestErrorException ( 'Request resource is not available.' );
		}
		
		/**
		 * 把一个file include进来
		 *
		 * @param string $path
		 *        	可以是绝对路径，也可以是app根目录的相对路径。
		 */
		public function loadFile($path) {
			return include $path;
		}
		
		/**
		 * 取得类所在的模块
		 *
		 * @param string $className        	
		 * @return array 数组中的第一个值是模块的别名（如果是模块内部调用，就是模块名），第二个值是去掉模块别名剩下的字符串。
		 */
		protected function getClassModule($className) {
			$pos = strpos ( $className, '\\' );
			return array (
					substr ( $className, 0, $pos ),
					substr ( $className, $pos + 1 ) 
			);
		}
	}
	
	ClassLoader::$HHP_DIR = 'hhp' . DIRECTORY_SEPARATOR;
	ClassLoader::$HFC_DIR = ClassLoader::$HHP_DIR . 'hfc' . DIRECTORY_SEPARATOR;
}