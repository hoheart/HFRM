<?php

namespace Framework\Facade;

use Framework\RequestContext;
use Framework\HFC\Log\Logger;

class Log {

	static public function r ($desc, $moduleName, RequestContext $context = null) {
		if ('' == $moduleName) {
			$module = Config::Instance()->get('app.moduleDir');
			$moduleName = basename($module);
		}
		
		$clientIp = '';
		if (null != $context) {
			$clientIp = $context->request->getClientIP();
		}
		
		Service::get('log')->log($desc, $moduleName, Logger::LOG_TYPE_RUN, Logger::LOG_LEVEL_FATAL, 'local', $clientIp);
	}

	static public function e ($desc, $moduleName, $level, RequestContext $context = null) {
		if ('' == $moduleName) {
			$module = Config::Instance()->get('app.moduleDir');
			$moduleName = basename($module);
		}
		
		$clientIp = '';
		if (null != $context) {
			$clientIp = $context->request->getClientIP();
		}
		
		Service::get('log')->log($desc, $moduleName, Logger::LOG_TYPE_ERROR, $level, 'local', $clientIp);
	}
}