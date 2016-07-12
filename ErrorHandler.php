<?php

namespace Framework;

use Framework\Facade\Service;
use Framework\HFC\Log\Logger;

class ErrorHandler {

	public function register2System () {
		// 关闭所有错误输出
		ini_set('display_errors', 'on');
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

	public function handleException (\Exception $e, RequestContext $context) {
		$this->handle(0, '', '', - 1, $e, array(), $context);
	}

	public function processError ($errno, $errstr, $errfile, $errline, array $errcontext) {
		$this->handle($errno, $errstr, $errfile, $errline, null, $errcontext);
	}

	public function handle ($errno, $errstr, $errfile, $errline, $e = null, $errcontext = array(), RequestContext $context = null) {
		if (null != $e) {
			$errno = $e->getCode();
			$errstr = $e->getMessage();
			$errfile = $e->getFile();
			$errline = $e->getLine();
		}
		// 永远不处理这两个错误，因为这才是php好用的地方。
		if (E_STRICT === $errno || E_NOTICE === $errno) {
			return;
		}
		
		$jsonDetail = self::GetErrorAsJsonDetail($errno, $errstr, $errfile, $errline, $e, $errcontext);
		
		// 记录日志
		$log = Service::get('log');
		$log->log($jsonDetail, 'framework', Logger::LOG_TYPE_ERROR, Logger::LOG_LEVEL_FATAL, $context);
		
		// 调用用户配置的错误处理
		$errConf = Config::Instance()->get('app.error_processor');
		if (! empty($errConf)) {
			$p = new $errConf();
			$p->handle($errno, $errstr, $errfile, $errline, $e, $errcontext, $context);
			
			return;
		}
		
		if (null != $context) {
			$err = $this->GetErrorByDebug($errno, $errstr, $errfile, $errline, $e);
			
			// 一旦出错，有可能服务器主动设置成了500
			$context->response->setStatusCode(200);
			
			App::Respond($context, null, $err);
		} else {
			// 直接输出到控制台，swoole会记录到日志。
			echo ("Error:$errno:$errstr.\n");
			echo ("In file:$errfile:$errline.\n\n");
		}
	}

	static public function GetErrorAsJsonDetail ($errno, $errstr, $errfile, $errline, $e = null, $errcontext = array()) {
		$node = array(
			'errcode' => $errno,
			'errstr' => $errstr,
			'errDetail' => array(
				'errfile' => $errfile,
				'errline' => $errline,
				'errcontext' => $errcontext
			)
		);
		if (null != $e) {
			$node['errcode'] = $e->getCode();
			$node['errstr'] = $e->getMessage();
			$node['backtrace'] = $e->getTrace();
		}
		
		return json_encode($node);
	}

	/**
	 * 根据是否是debug输出error
	 *
	 * @param int $errno        	
	 * @param string $errstr        	
	 * @param string $errfile        	
	 * @param int $errline        	
	 * @param \Exception $e        	
	 * @param array $errcontext        	
	 */
	static public function GetErrorByDebug ($errno, $errstr, $errfile, $errline, $e = null, $errcontext = array()) {
		$node = array(
			'errcode' => $errno,
			'errstr' => $errstr
		);
		if (null != $e) {
			$node['errcode'] = $e->getCode();
			$node['errstr'] = $e->getMessage();
		}
		
		if (Config::Instance()->get('app.debug')) {
			if (null == $e) {
				$node['errDetail'] = array(
					'errfile' => $errfile,
					'errline' => $errline,
					'errcontext' => $errcontext
				);
			} else {
				$node['backtrace'] = $e->getTrace();
			}
		} else {
			if ($node['errcode'] < 400000 || $node['errcode'] >= 500000) {
				$node['errcode'] = 500000;
				$node['errstr'] = 'system error.';
			}
		}
		
		return $node;
	}
}