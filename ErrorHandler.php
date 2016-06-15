<?php

namespace Framework;

use Framework\View\View;
use Framework\Facade\Service;
use Framework\HFC\Log\Logger;
use Framework\Facade\Redirect;
use Framework\Response\HttpResponse;

class ErrorHandler {

	public function register2System () {
		// 关闭所有错误输出
		ini_set('display_errors', 'Off');
		error_reporting(0);
		
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
		$this->handle(0, '', '', - 1, $e);
	}

	public function processError ($errno, $errstr, $errfile, $errline, array $errcontext) {
		$this->handle($errno, $errstr, $errfile, $errline, null, $errcontext);
	}

	public function handle ($errno, $errstr, $errfile, $errline, $e = null, $errcontext = array()) {
		if (null != $e) {
			$errno = $e->getCode();
		}
		// 永远不处理这两个错误，这是php好用的地方。
		if (E_STRICT === $errno || E_NOTICE === $errno) {
			return;
		}
		
		$jsonDetail = self::GetErrorJsonDetail($errno, $errstr, $errfile, $errline, $e, $errcontext);
		
		// 记录日志
		$log = Service::get('log');
		$log->log($jsonDetail, Logger::LOG_TYPE_ERROR, '', Logger::LOG_LEVEL_FATAL);
		
		// 调用用户配置的错误处理
		$errConf = Config::Instance()->get('app.error_processor');
		if (! empty($errConf)) {
			$p = new $errConf();
			$p->handle($errno, $errstr, $errfile, $errline, $e, $errcontext);
			
			return;
		}
		
		$req = App::Instance()->getRequest();
		if ($req && $req->isAjaxRequest()) {
			if (Config::Instance()->get('app.debug')) {
				$json = $jsonDetail;
			} else {
				$json = self::GetErrorJsonByDebug($errno, $errstr, $errfile, $errline, $e, $errcontext);
			}
			$resp = new HttpResponse($json);
			App::Instance()->respond($resp);
		} else {
			if (Config::Instance()->get('app.debug')) {
				if (null != $e) {
					$errstr = $e->getMessage();
					$errfile = $e->getFile();
					$errline = $e->getLine();
				}
				$out = App::Instance()->getOutputStream();
				if (null != $out) {
					$out->write("Error:$errno:$errstr.");
					$out->write('<br>');
					$out->write("In file:$errfile:$errline.");
					$out->write('<br>');
					$out->write('<pre>');
					
					ob_start();
					if (null === $e) {
						// 当调用栈太大时，会导致内存达到配置的最大内存限制
						debug_print_backtrace(~ DEBUG_BACKTRACE_IGNORE_ARGS);
					} else {
						print_r($e);
					}
					$out->write(ob_get_contents());
					ob_clean();
					
					$out->write('</pre>');
					$out->close();
				}
			} else {
				Redirect::to('/error');
			}
		}
		
		if (Config::Instance()->get('app.debug')) {
			exit(- 3);
		}
	}

	static public function GetErrorJsonDetail ($errno, $errstr, $errfile, $errline, $e = null, $errcontext = array()) {
		$node = array(
			'errcode' => $errno,
			'errstr' => $errstr,
			'errDetail' => array(
				'errfile' => $errfile,
				'errline' => $errline,
				'errcontext' => $errcontext
			),
			'data' => null
		);
		if (null != $e) {
			$node['errcode'] = $e->getCode();
			$node['errstr'] = $e->getMessage();
			$node['errDetail'] = $e->__toString();
		}
		
		return json_encode($node);
	}

	static public function GetErrorJsonByDebug ($errno, $errstr, $errfile, $errline, $e = null, $errcontext = array()) {
		$view = new View('', '', View::VIEW_TYPE_JSON);
		if (Config::Instance()->get('app.debug')) {
			return self::GetErrorJsonDetail($errno, $errstr, $errfile, $errline, $e, $errcontext);
		} else {
			$node = array(
				'errcode' => $errno,
				'errstr' => $errstr,
				'data' => null
			);
			if (null != $e) {
				$node['errcode'] = $e->getCode();
				$node['errstr'] = $e->getMessage();
			}
			if ($node['errcode'] < 400000 || $node['errcode'] >= 500000) {
				$node['errcode'] = 500000;
				$node['errstr'] = 'system error.';
				$node['data'] = null;
			}
			
			return json_encode($node);
		}
	}
}