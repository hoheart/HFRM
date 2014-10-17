<?php

namespace test {

	use test\AppTest\ClassLoaderTest;
	use hhp\HttpRequest;
	use hhp\App;

	/**
	 * 对hhp框架的App类进行测试
	 *
	 * @author Hoheart
	 *        
	 */
	class AppTest extends AbstractTest {
		public function test() {
			$cl = new ClassLoaderTest ();
			$cl->test ();
			
			$this->Instance ();
			$this->getActionName ();
			$this->run ();
			$this->getRoute ();
			$this->route ();
			$this->getService ();
			$this->trigger ();
		}
		public function Instance() {
			$app = App::Instance ();
			if ($app instanceof App) {
				return true;
			}
			
			$this->thorwError ( '', __METHOD__, __LINE__ );
		}
		public function getActionName() {
			$r = new HttpRequest ();
			$defaultControllerConf = array (
					'contorller' => 'TestC',
					'action' => 'TestA' 
			);
			
			$app = HApp::Instance ();
			if ('TestA' != $app->getActionName ( $r, $defaultControllerConf )) {
				$this->thorwError ( '', __METHOD__, __LINE__ );
			}
			
			$r->setVal ( 'c', 'aa' );
			if ('TestA' != $app->getActionName ( $r, $defaultControllerConf )) {
				$this->thorwError ( '', __METHOD__, __LINE__ );
			}
			
			$r->setVal ( 'a', 'bb' );
			if ('bb' != $app->getActionName ( $r, $defaultControllerConf )) {
				$this->thorwError ( '', __METHOD__, __LINE__ );
			}
			
			$r->setVal ( 'c', null );
			if ('TestA' != $app->getActionName ( $r, $defaultControllerConf )) {
				$this->thorwError ( '', __METHOD__, __LINE__ );
			}
			
			return true;
		}
		public function run() {
			$app = HApp::Instance ();
			
			// 测试工作目录
			if (getcwd () . DIRECTORY_SEPARATOR != HApp::getRootDir ()) {
				$this->thorwError ( '', __METHOD__, __LINE__ );
			}
			// 测试模块配置文件合并。TestSub目录放在本模块的配置里了，所以，只有合并成功，前面的测试ClassLoader的才会通过，不需要重复测试。
			// 测试Action配置文件合并。由于pre_executer和later_executer的配置是放在下面的action测试里的，所以，不需要重复测试。
			
			// 测试createController。createController如果不成功就调用不到该这儿来了，所以不用重复测试。
			// 测试pre_executer和later_executer
			ob_start ();
			$url = 'http://' . $_SERVER ['SERVER_NAME'] . ':' . $_SERVER ['SERVER_PORT'] . '/' . $_SERVER ['SCRIPT_NAME'] . '?c=TestController&a=executerTester';
			$ret = file_get_contents ( $url );
			if ('12aa34' != trim ( $ret )) {
				$this->thorwError ( '', __METHOD__, __LINE__ );
			}
			
			return true;
		}
		public function getRoute() {
			return true; // run函数已经测了。
		}
		public function route() {
			return true; // run函数已经测了。
		}
		public function getService() {
			$app = HApp::Instance ();
			$s1 = $app->getService ( 'db' );
			if (! $s1 instanceof \Hfc\Database\DatabaseClient) {
				$this->thorwError ( '', __METHOD__, __LINE__ );
			}
			$s2 = $app->getService ( 'db' );
			if ($s1 != $s2) { // 要测试是否每次取得都是同一个对象，这样同时也保证了ServiceManager对象在App里是唯一实例。
				$this->thorwError ( '', __METHOD__, __LINE__ );
			}
			
			return true;
		}
		public function trigger() {
			ob_start ();
			$url = 'http://' . $_SERVER ['SERVER_NAME'] . ':' . $_SERVER ['SERVER_PORT'] . '/' . $_SERVER ['SCRIPT_NAME'] . '?c=TestController&a=eventTester';
			$ret = file_get_contents ( $url );
			if ('b0a123d0c1401560178' != trim ( $ret )) {
				$this->thorwError ( '', __METHOD__, __LINE__ );
			}
			
			return true;
		}
		public function executerTester($do) {
			echo 'aa';
			
			if (! $do instanceof \stdClass) {
				$this->thorwError ( '', __METHOD__, __LINE__ );
			}
			
			if ('edf' != $do->edf) {
				$this->thorwError ( '', __METHOD__, __LINE__ );
			}
			
			$v = new View ();
			
			return $v;
		}
		public function eventTester() {
			$app = \hhp\App::Instance ();
			
			$app->trigger ( 'testEvent', $this, array (
					'a',
					'b' 
			) );
			$app->trigger ( 'testEvent1', $this, array (
					'c',
					'd' 
			) );
			
			$e1 = new Event ( $this );
			$app->trigger ( $e1 );
			
			$e2 = new Event1 ( $this );
			$app->trigger ( $e2 );
		}
	}
}

namespace test\AppTest {

	use hhp\HttpRequest;
	use hfc\database\DatabaseClientFactory;
	use test\AbstractTest;
	use test\TestApi;
	use TestSub\TestSubApi;

	class ClassLoaderTest extends AbstractTest {
		public function test() {
			$this->construct ();
			$this->register2System ();
			$this->autoload ();
		}
		public function construct() {
			new \hhp\App\ClassLoader ();
		}
		public function register2System() {
			$l = new \hhp\App\ClassLoader ();
			$l->register2System ();
			
			// 如果能new，表示注册成功了
			$r = new HttpRequest ();
		}
		public function autoload() {
			$l = new \hhp\App\ClassLoader ();
			$l->register2System ();
			
			// 先测试hhp
			$r = new HttpRequest ();
			// 再测试hfc
			$db = new DatabaseClientFactory ();
			// 再测试本模块
			$c = new TestApi ();
			// 再测试引用模块
			$api = new TestSubApi ();
			$ret = $api->api ();
			if ('api' != $ret) {
				$this->throwError ( '', __METHOD__, __LINE__ );
			}
			
			// 测试模块没有开启
			if ($l->autoload ( 'TestSub1\TestSub1Api' )) {
				$this->throwError ( '', __METHOD__, __LINE__ );
			}
			
			// 测试没有依赖
			if ($l->autoload ( 'TestSub2\TestSub2Api' )) {
				$this->throwError ( '', __METHOD__, __LINE__ );
			}
			
			// 测试调用没有开启接口的类
			if ($l->autoload ( 'TestSub\TestSubInnerApi' )) {
				$this->throwError ( '', __METHOD__, __LINE__ );
			}
			
			return true;
		}
	}
}
?>