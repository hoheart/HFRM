<?php

namespace Framework {

	use Framework\App\ClassLoader;
	use Framework\Request\IRequest;
	use Framework\Config;
	use Framework\Output\IOutputStream;
	use Framework\Exception\EndAppException;
	use Framework\Controller\RPCServiceController;

	/**
	 * 目前的设计为每次请求都start然后stop。
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
		 * @var ServiceManager
		 */
		protected $mServiceManager = null;

		/**
		 * 构造函数，创建ClassLoader，并调用其register2System。
		 */
		protected function __construct () {
			// 切换到App目录。
			chdir(self::$ROOT_DIR);
		}

		/**
		 * 运行应用程序
		 *
		 * 因为是服务层框架，所有的请求均来自http（均以服务的形式出现），命令行的请求应该在前端实现。
		 *
		 * @param IRequest $request
		 *        	主要给swoole用的。如果是swoole，考虑到重入问题，不能用全局变量，在外面创建号request传进来
		 * @param IOutputStream $out        	
		 */
		public function run (IHttpRequest $request = null, IOutputStream $output = null) {
			$context = new RequestContext($request, $output);
			
			try {
				$this->start();
				
				// 去掉了路由，直接访问固定controller。对于要访问哪个模块的哪个接口，直接由controller决定。
				// 去掉了pre_executor，没用，如果以后需要注入（AOP），再想办法解决。
				// 还是保留了controller层，目的是框架就是框架，只完成service、dependency等框架该完成的功能。
				$ctrl = new RPCServiceController();
				$ctrl->serve($context);
			} catch (\Exception $e) {
				// 出错了，赶紧结束掉，该回滚的回滚，释放错误的资源占用。而且，handleException在debug模式下会退出。
				$this->stop(false);
				$this->mErrorHandler->handleException($e, $context);
			}
			
			// 不需要记录日志，由controller完成。
			
			return null === $e ? true : false;
		}

		static public function Respond (RequestContext $context, $obj, $err = array()) {
			$rpcp = App::Instance()->getRpcProtocol();
			$data = $rpcp->packRet($obj, $err);
			
			$context->output->write($data);
			$context->output->close();
			
			App::Instance()->stop();
			
			$context->add('hasResponded', true);
		}

		public function getRpcProtocol () {
			$rpcCls = Config::Instance()->get('app.rpcProtocol');
			$rpcp = $rpcCls::Instance();
			
			return $rpcp;
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
			date_default_timezone_set(Config::Instance()->get('app.localTimezone'));
		}

		public function start () {
			if (null != $this->mServiceManager) {
				$this->mServiceManager->start();
			}
		}

		public function stop ($normal = true) {
			if (null != $this->mServiceManager) {
				$this->mServiceManager->stop($normal);
			}
		}

		public function getVersion () {
			return $this->getConfigValue('version');
		}

		public function getServiceManager () {
			if (null == $this->mServiceManager) {
				$this->mServiceManager = new ServiceManager();
				$this->mServiceManager->start();
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

	/**
	 * 根据名字空间，include类的定义。此类的目的就是封装include函数。
	 * PHP没有内部类的语法，把代码写在一个文件里解决。
	 *
	 * @author Hoheart
	 *        
	 */
	class ClassLoader {

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
			if (DIRECTORY_SEPARATOR == '\\') {
				$path = $className . '.php';
			} else {
				$path = str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
			}
			
			include_once App::$ROOT_DIR . $path; // 如果用require，就会产生一个系统错误，而用户自定义错误就截取不到了。
		}
	}
}