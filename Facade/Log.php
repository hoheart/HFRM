<?php

namespace Framework\Facade;

use Framework\RequestContext;
use Framework\HFC\Log\Logger;

class Log {

	static public function r ($desc, RequestContext $context = null) {
		Service::get('log')->log($desc, Logger::LOG_TYPE_RUN, Logger::LOG_LEVEL_FATAL, $context);
	}

	static public function e ($desc, $level, RequestContext $context = null) {
		Service::get('log')->log($desc, Logger::LOG_TYPE_ERROR, $level, $context);
	}
}