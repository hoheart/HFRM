<?php

namespace test;

/**
 * 对hhp框架的App类进行测试
 *
 * @author Hoheart
 *        
 */
class ErrorHandlerTest extends \test\AbstractTest {

	public function test () {
		$this->register2System();
		$this->handleShutdown();
		$this->handleException();
		$this->processError();
		$this->handle();
	}

	public function register2System () {
		$url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] .
				 '/test/errorHandlerTest/register2System';
		$ret = file_get_contents($url);
		$errstr = 'Error:390:aa';
		if ($errstr == substr($ret, 0, strlen($errstr) - 1)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function handleShutdown () {
		$url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] .
				 '/test/errorHandlerTest/handleShutdown';
		$ret = file_get_contents($url);
		$errstr = '11Error:2:include():'; // 如果11在前，说明错误控制实在echo之后，应该就是shutdown时发生的了。
		if ($errstr == substr($ret, 0, strlen($errstr) - 1)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function handleException () {
		$url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] .
				 '/test/errorHandlerTest/handleException';
		$ret = file_get_contents($url);
		$errstr = 'Error:391:bb'; // 如果11在前，说明错误控制实在echo之后，应该就是shutdown时发生的了。
		if ($errstr == substr($ret, 0, strlen($errstr) - 1)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}

	public function processError () {
		$this->handle();
	}

	public function handle () {
		$url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] .
				 '/test/errorHandlerTest/handle';
		$ret = file_get_contents($url);
		$arr = json_decode($ret, true);
		if (! is_array($arr) || empty($arr)) {
			$this->throwError('', __METHOD__, __LINE__);
		}
	}
}