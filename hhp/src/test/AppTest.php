<?php

namespace test {

	use test\AppTest\ClassLoaderTest;
	use hhp\App;

	/**
	 * 对hhp框架的App类进行测试
	 *
	 * @author Hoheart
	 *        
	 */
	class AppTest extends AbstractTest {

		public function test () {
			$cl = new ClassLoaderTest();
			$cl->test();
			
			$this->Instance();
			$this->getVersion();
			$this->getCurrentController();
			$this->getController();
			$this->getRequest();
			$this->run();
			$this->getService();
			$this->trigger();
			$this->getModuleConf();
			$this->getConfigValue();
		}

		public function Instance () {
			$app = App::Instance();
			if (! $app instanceof App) {
				$this->throwError('', __METHOD__, __LINE__);
			}
			
			// 测试工作目录
			if (getcwd() . DIRECTORY_SEPARATOR != App::$ROOT_DIR) {
				$this->throwError('', __METHOD__, __LINE__);
			}
		}

		public function getVersion () {
			if ('3.34.5' != App::Instance()->getVersion()) {
				$this->throwError('', __METHOD__, __LINE__);
			}
		}

		public function getCurrentController () {
			if (! App::Instance()->getCurrentController() instanceof \test\controller\TestController) {
				$this->throwError('', __METHOD__, __LINE__);
			}
		}

		public function getController () {
			if (! App::Instance()->getController() instanceof \test\controller\TestController) {
				$this->throwError('', __METHOD__, __LINE__);
			}
		}

		public function getRequest () {
			if (! App::Instance()->getRequest() instanceof \hhp\IRequest) {
				$this->throwError('', __METHOD__, __LINE__);
			}
		}

		public function run () {
			$this->testDirection();
			
			// 测试模块配置文件合并。TestSub目录放在本模块的配置里了，所以，只有合并成功，前面的测试ClassLoader的才会通过，不需要重复测试。
			// 测试Action配置文件合并。由于pre_executor和later_executor的配置是放在单独的action测试里的，所以，不需要重复测试。
			
			// 测试pre_executor和later_executor
			$this->testExecutor();
		}

		protected function testDirection () {
			$url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] .
					 '/test/runTest/testRedirection';
			$ret = file_get_contents($url);
			if ('redirection148' != $ret) {
				$this->throwError('', __METHOD__, __LINE__);
			}
			// 找不到controller，就找default controller，所以与上面效果一样。
			$url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] .
					 '/runTest/testRedirection';
			$ret = file_get_contents($url);
			if ('redirection148' != $ret) {
				$this->throwError('', __METHOD__, __LINE__);
			}
			
			$url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] .
					 '/aaa/test/runTest/testRedirection';
			$ret = file_get_contents($url);
			if ('redirection148' == $ret) {
				$this->throwError('', __METHOD__, __LINE__);
			}
			
			// 不用测试默认controller和action了，能调用到这儿，默认肯定没问题
			
			$url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] .
					 '/test/runTest/testArgv?name=ddfl90';
			$ret = file_get_contents($url);
			if ('ddfl90' != $ret) {
				$this->throwError('', __METHOD__, __LINE__);
			}
		}

		protected function testExecutor () {
			$url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] .
					 '/test/runTest/testExecutor';
			$ret = file_get_contents($url);
			if ('later2' != $ret) {
				$this->throwError('', __METHOD__, __LINE__);
			}
		}

		public function getService () {
			$app = App::Instance();
			$s1 = $app->getService('db');
			if (! $s1 instanceof \hfc\database\DatabaseClient) {
				$this->throwError('', __METHOD__, __LINE__);
			}
			$s2 = $app->getService('db');
			if ($s1 != $s2) { // 要测试是否每次取得都是同一个对象，这样同时也保证了ServiceManager对象在App里是唯一实例。
				$this->throwError('', __METHOD__, __LINE__);
			}
			
			return true;
		}

		public function trigger () {
			$url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] .
					 '/test/triggerTest/trigger';
			$ret = file_get_contents($url);
			if ('b0a123d0c1401560178' != $ret) {
				$this->throwError('', __METHOD__, __LINE__);
			}
			
			return true;
		}

		public function getModuleConf () {
			$app = App::Instance();
			
			// 测试是否是直接返回的当前controller的config
			$testConf = $app->getModuleConf('test');
			if (! empty($testConf['default_module'])) {
				$this->throwError('', __METHOD__, __LINE__);
			}
			if ('TestSub2' != $testConf['module']['TestSub2']['name']) {
				$this->throwError('', __METHOD__, __LINE__);
			}
			// 测试是否只能调用一次
			$testSub1Conf = $app->getModuleConf('TestSub1');
			if ($testSub1Conf == $testConf) {
				$this->throwError('', __METHOD__, __LINE__);
			}
		}

		public function getConfigValue () {
			$app = App::Instance();
			
			// 测试是否是直接返回的当前controller的config
			if ('3.34.5' != $app->getConfigValue('version')) {
				$this->throwError('', __METHOD__, __LINE__);
			}
			if ('TestSub2' != $app->getConfigValue('module')['TestSub2']['name']) {
				$this->throwError('', __METHOD__, __LINE__);
			}
		}
	}
}

namespace test\AppTest {

	use hhp\HttpRequest;
	use hfc\database\DatabaseClientFactory;
	use test\AbstractTest;
	use test\TestApi;
	use TestSub\TestSubApi;
	use hhp\exception\RequestErrorException;
	use hhp\exception\ModuleNotAvailableException;
	use hhp\App;

	class ClassLoaderTest extends AbstractTest {

		public function test () {
			$this->construct();
			$this->register2System();
			$this->autoload();
			$this->loadController();
			$this->loadFile();
		}

		public function construct () {
			new \hhp\App\ClassLoader();
		}

		public function register2System () {
			$l = new \hhp\App\ClassLoader();
			$l->register2System();
			
			// 如果能new，表示注册成功了
			$r = new HttpRequest();
		}

		public function autoload () {
			$l = new \hhp\App\ClassLoader();
			$l->register2System();
			
			// 先测试hhp
			$r = new HttpRequest();
			// 再测试hfc
			$db = new DatabaseClientFactory();
			
			// 测试框架调用外面的模块
			// 分三中情况：executor的调用、Service的调用、Trigger的调用，风别放在这三个功能处测试，此处不重复测试。
			
			// 再测试本模块
			$c = new TestApi();
			// 再测试引用模块
			$api = new TestSubApi();
			$ret = $api->api();
			if ('api' != $ret) {
				$this->throwError('', __METHOD__, __LINE__);
			}
			
			// 测试模块没有开启
			if (class_exists('TestSub1\TestSub1Api')) {
				$this->throwError('', __METHOD__, __LINE__);
			}
			// 测试没有依赖
			if (class_exists('TestSub2\TestSub2Api')) {
				$this->throwError('', __METHOD__, __LINE__);
			}
			// 测试调用没有开启接口的类
			if (class_exists('TestSub\TestSubInnerApi')) {
				$this->throwError('', __METHOD__, __LINE__);
			}
			
			// 测试调用别名与模块名及路径不一致的情况
			try {
				$obj = App::create('innerDirSub\InnerSubApi');
				if (! is_object($obj)) {
					throw new \Exception('');
				}
			} catch (\Exception $e) {
				$this->throwError('', __METHOD__, __LINE__);
			}
			
			return true;
		}

		public function loadController () {
			$l = new \hhp\App\ClassLoader();
			
			// 测试模块不存在
			$testPass = false;
			try {
				$l->loadController('TestSub1', 'dfdfsd');
			} catch (ModuleNotAvailableException $e) {
				$testPass = true;
			} catch (\Exception $e) {
			}
			if (! $testPass) {
				$this->throwError('', __METHOD__, __LINE__);
			}
			
			// 测试controller存在
			$testPass = false;
			$ctrlClassName = $l->loadController('TestSub2', 'testSub2');
			if ('TestSub2\controller\TestSub2Controller' == $ctrlClassName) {
				$testPass = true;
			} else {
				$this->throwError('', __METHOD__, __LINE__);
			}
			
			// 测试controller不存在
			$testPass = false;
			try {
				$l->loadController('TestSub2', 'dffd');
			} catch (RequestErrorException $e) {
				$testPass = true;
			} catch (\Exception $e) {
			}
			if (! $testPass) {
				$this->throwError('', __METHOD__, __LINE__);
			}
			
			// 测试controller没开启
			$testPass = false;
			try {
				$l->loadController('TestSub', 'testSubDisabled');
			} catch (RequestErrorException $e) {
				$testPass = true;
			} catch (\Exception $e) {
			}
			if (! $testPass) {
				$this->throwError('', __METHOD__, __LINE__);
			}
		}

		public function loadFile () {
			$l = new \hhp\App\ClassLoader();
			$str = $l->loadFile('test/loadFileData.php');
			if ('alsdkfjasldfj;' != $str) {
				$this->throwError('', __METHOD__, __LINE__);
			}
		}
	}
}
?>