<?php

namespace Test {

	use Test\App\ClassLoader;
	use Hhp\HttpRequest;
	use Hhp\App;

	class AppTest {

		public function test () {
			$cl = new ClassLoader();
			if (! $cl->test()) {
				return false;
			}
			
			if (! $this->Instance() || ! $this->getActionName() || ! $this->run() || ! $this->getRoute() ||
					 ! $this->route() || ! $this->getService() || ! $this->trigger()) {
				return false;
			}
			
			return true;
		}

		public function Instance () {
			$app = HApp::Instance();
			if ($app instanceof HApp) {
				return true;
			}
			
			return false;
		}

		public function getActionName () {
			$r = new HttpRequest();
			$defaultControllerConf = array(
				'contorller' => 'TestC',
				'action' => 'TestA'
			);
			
			$app = HApp::Instance();
			if ('TestA' != $app->getActionName($r, $defaultControllerConf)) {
				return false;
			}
			
			$r->setVal('c', 'aa');
			if ('TestA' != $app->getActionName($r, $defaultControllerConf)) {
				return false;
			}
			
			$r->setVal('a', 'bb');
			if ('bb' != $app->getActionName($r, $defaultControllerConf)) {
				return false;
			}
			
			$r->setVal('c', null);
			if ('TestA' != $app->getActionName($r, $defaultControllerConf)) {
				return false;
			}
			
			return true;
		}

		public function run () {
			$app = HApp::Instance();
			
			// 测试工作目录
			if (getcwd() . DIRECTORY_SEPARATOR != HApp::getRootDir()) {
				return false;
			}
			// 测试模块配置文件合并。TestSub目录放在本模块的配置里了，所以，只有合并成功，前面的测试ClassLoader的才会通过，不需要重复测试。
			// 测试Action配置文件合并。由于pre_executer和later_executer的配置是放在下面的action测试里的，所以，不需要重复测试。
			
			// 测试createController。createController如果不成功就调用不到该这儿来了，所以不用重复测试。
			// 测试pre_executer和later_executer
			ob_start();
			$url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . '/' .
					 $_SERVER['SCRIPT_NAME'] . '?c=TestController&a=executerTester';
			$ret = file_get_contents($url);
			if ('12aa34' != trim($ret)) {
				return false;
			}
			
			return true;
		}

		public function getRoute () {
			return true; // run函数已经测了。
		}

		public function route () {
			return true; // run函数已经测了。
		}

		public function getService () {
			$app = HApp::Instance();
			$s1 = $app->getService('db');
			if (! $s1 instanceof \Hfc\Database\DatabaseClient) {
				return false;
			}
			$s2 = $app->getService('db');
			if ($s1 != $s2) { // 要测试是否每次取得都是同一个对象，这样同时也保证了ServiceManager对象在App里是唯一实例。
				return false;
			}
			
			return true;
		}

		public function trigger () {
			ob_start();
			$url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . '/' .
					 $_SERVER['SCRIPT_NAME'] . '?c=TestController&a=eventTester';
			$ret = file_get_contents($url);
			if ('b0a123d0c1401560178' != trim($ret)) {
				return false;
			}
			
			return true;
		}
	}
}

namespace Test\App {

	use Hhp\HttpRequest;
	use test\ServiceManager;
	use TestSub\TestSub;
	use Hfc\Database\DatabaseClientFactory;

	class ClassLoader {

		public function test () {
			if (! $this->construct() || ! $this->register2System() || ! $this->autoload()) {
				return false;
			}
			
			return true;
		}

		public function construct () {
			$l = new \Hhp\App\ClassLoader();
			return true;
		}

		public function register2System () {
			$l = new \Hhp\App\ClassLoader();
			$l->register2System();
			
			$r = new HttpRequest();
			
			return true;
		}

		public function autoload () {
			$l = new \Hhp\App\ClassLoader();
			$l->register2System();
			
			// 先测试Hhp
			$r = new HttpRequest();
			// 再测试Hfc
			$db = new DatabaseClientFactory();
			// 再测试本模块
			$c = new ServiceManager();
			// 再测试引用模块
			$sub = new TestSub();
			
			return true;
		}
	}
}
?>