<?php

namespace Framework;

use Framework\View\JsonRender;
use Framework\Exception\UserErrcode;
use Framework\Request\HttpRequest;
use Framework\View\View;
use Framework\Facade\Service;
use HFC\Log\Logger;
use Framework\Facade\Redirect;

class ErrorHandler {

	public function register2System () {
		// 关闭所有错误输出
		ini_set('display_errors', 'On');
		error_reporting(- 1);
		
		set_error_handler(array(
			$this,
			'processError'
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
		$this->handle($errno, $errstr, $errfile, $errline, null, $errcontext);
	}

	public function handle ($errno, $errstr, $errfile, $errline, $e = null, $errcontext = array()) {
		$notProcessError = array(
			E_NOTICE,
			E_STRICT
		);
		
		$errcode = UserErrcode::ErrorOK;
		if (in_array($errno, $notProcessError)) {
			return;
		}
		
		$log = Service::get('log');
		$logstr = '';
		if (! empty($errstr)) {
			$logstr = "errno: $errno , errstr: $errstr On: $errfile:$errline";
		}
		if (null != $e) {
			$logstr .= (string) $e;
		}
		$log->log($logstr, Logger::LOG_TYPE_ERROR, '', Logger::LOG_LEVEL_FATAL);
		
		$errConf = Config::Instance()->get('app.error_processor');
		if (! empty($errConf)) {
			$p = new $errConf();
			$p->handle($errno, $errstr, $errfile, $errline, $e, $errcontext);
			
			return;
		}
		
		if (HttpRequest::isAjaxRequest()) {
			if (Config::Instance()->get('app.debug')) {
				if (is_array($e)) {
					$e[] = $errstr;
					$this->rendJson($errno, $errstr, $e);
				} else {
					$this->rendJson($errno, $errstr, $e);
				}
			} else {
				$this->rendJson($errno, $errstr);
			}
		} else {
			if (Config::Instance()->get('app.debug')) {
				ob_clean();
				
				$out = App::Instance()->getOutputStream();
				$out->write("Error:$errno:$errstr.");
				$out->write('<br>');
				$out->write("In file:$errfile:$errline.");
				$out->write('<br>');
				$out->write('<pre>');
				$out->write('<br>');
				
				ob_start();
				print_r($e);
				if (! $e instanceof \Exception) {
					// 当调用栈太大时，会导致内存达到配置的最大内存限制
					debug_print_backtrace();
				}
				$out->write(ob_get_contents());
				ob_clean();
				
				$out->write('</pre>');
				$out->close();
			} else {
				try {
					// echo $errno;
					// Log::e("Error:$errno:$errstr.\nIn
					// file:$errfile:$errline.");
				} catch (\Exception $e) {
				}
				
				// $this->rendJson($errno, $errstr);
				Redirect::to('/error');
			}
		}
	}

	protected function rendJson ($errno, $errstr, $e = null) {
		$view = new View('', 'common::Common.frame', View::VIEW_TYPE_JSON);
		
		if (Config::Instance()->get('app.debug') || ($errno > 400000 && $errno <= 500000)) {
			$view->assign('errcode', $errno);
			$view->assign('errstr', $errstr);
			$view->assign('data', $e);
		} else {
			$view->assign('errcode', 500000);
			$view->assign('errstr', 'system error.');
		}
		
		$render = new JsonRender();
		$render->render($view);
	}
}