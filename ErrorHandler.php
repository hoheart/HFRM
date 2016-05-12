<?php

namespace hhp;

use hhp\view\JsonRender;
use hhp\exception\UserErrcode;

class ErrorHandler {

	public function register2System () {
		// 关闭所有错误输出
		ini_set('display_errors', 'Off');
		error_reporting(0);
		
		set_error_handler(array(
			$this,
			'handle'
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
		if (null != $errinfo) {
			$this->handle($errinfo['type'], $errinfo['message'], $errinfo['file'], $errinfo['line']);
		}
	}

	public function handleException (\Exception $e) {
		$this->handle($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine(), $e);
	}

	public function processError ($errno, $errstr, $errfile, $errline, array $errcontext) {
		$this->handle($errno, $errstr, $errfile, $errline, $errcontext);
	}

	public function handle ($errno, $errstr, $errfile, $errline, $e = null) {
		$notProcessError = array(
			E_NOTICE,
			E_STRICT
		);
		
		$errcode = UserErrcode::ErrorOK;
		if (in_array($errno, $notProcessError)) {
			return;
		}
		
		if (App::Instance()->getConfigValue('debug')) {
			echo "Error:$errno:$errstr.";
			echo '<br>';
			echo "In file:$errfile:$errline.";
			echo '<br>';
			echo '<pre>';
			print_r($e);
			echo '</pre>';
		} else {
			try {
				// echo $errno;
				// Log::e("Error:$errno:$errstr.\nIn file:$errfile:$errline.");
			} catch (\Exception $e) {
			}
			
			header('Nothing', '', 200); // 因为到这儿说明脚本出现了致命错误，虽然handle了，但还是会抛出http错误码500，所以这儿手动改一下。
			
			$render = new JsonRender();
			
			$outErrcode = 0;
			if ($errno > 0 && ($errno < 400000 || $errno >= 500000)) {
				// 一旦出现致命错误，之前的chdir就没用了。
				$render->renderLayout(null, App::$ROOT_DIR . 'common/view/layout.php', 50000, 
						'System error, the Administrator has informed, looking for other pages.');
			} else {
				$outErrcode = $errno;
				$outErrstr = $errstr;
				$render->renderLayout(null, App::$ROOT_DIR . 'common/view/layout.php', $outErrcode, $outErrstr);
			}
		}
		
		exit(0);
	}
}
?>