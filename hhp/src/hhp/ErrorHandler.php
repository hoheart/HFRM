<?php

namespace hhp;

class ErrorHandler {

	public function register2System () {
		// 关闭所有错误输出
		ini_set('display_errors', 'Off');
		error_reporting(0);
		
		set_error_handler(array(
			$this,
			'handleError'
		), E_ALL | E_STRICT);
		set_exception_handler(array(
			$this,
			'handleException'
		));
		register_shutdown_function(array(
			$this,
			'handleShutdown'
		));
	}

	public function handleShutdown () {
		$errinfo = error_get_last();
		$this->handle($errinfo['type'], $errinfo['message'], $errinfo['file'], $errinfo['line']);
	}

	public function handleException (\Exception $e) {
		$this->handle($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), $e);
	}

	public function processError ($errno, $errstr, $errfile, $errline, array $errcontext) {
		$this->handle($errno, $errstr, $errfile, $errline, $errcontext);
	}

	public function handle ($errno, $errstr, $errfile, $errline, $e = null) {
		$notProcessError = array(
			E_NOTICE
		);
		if (in_array($errno, $notProcessError)) {
			return;
		}
		
		echo "Error:$errno:$errstr.";
		echo '<br>';
		echo "In file:$errfile:$errline.";
		echo '<br>';
		echo '<pre>';
		print_r($e);
		echo '</pre>';
		
		exit();
	}
}
?>