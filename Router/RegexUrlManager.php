<?php

/**
 * Created by PhpStorm.
 * User: HeYanLong
 * Date: 2016/3/1
 * Time: 14:07
 */
namespace Framework\Router;

use Framework\Config;

class RegexUrlManager {
	public $suffix;
	private $rules = [];
	public static $regexUrlManager = null;

	public static function Instance () {
		if (self::$regexUrlManager == null) {
			self::$regexUrlManager = new self();
			$rules = Config::Instance()->get('rules');
			self::$regexUrlManager->rules = self::$regexUrlManager->buildRules($rules);
		}
		return self::$regexUrlManager;
	}

	private function buildRules ($rules) {
		$compiledRules = [];
		$verbs = 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS';
		
		foreach ($rules as $key => $rule) {
			
			if (preg_match("/^((?:($verbs),)*($verbs))\\s+(.*)$/", $key, $matches)) {
				$verb = explode(',', $matches[1]);
				// rules that do not apply for GET requests should not be use to
				// create urls
				if (! in_array('GET', $rule['verb'])) {
					$mode = RegexRouter::PARSING_ONLY;
				}
				$key = $matches[4];
			}
			
			$compiledRules[] = new RegexRouter($key, $rule, $verb, $mode);
		}
		return $compiledRules;
	}

	public function getRoute ($request) {
		try {
			$route = $this->parseRequest(new Request());
		} catch (\Exception $e) {
			$route = null;
		}
		
		if ($route !== null) {
			// 处理0
			$pathArr = explode('/', $route[0]);
			$action = end($pathArr);
			array_pop($pathArr);
			$path = '';
			foreach ($pathArr as $key => $value) {
				if ($key == 0) {
					$value .= '\\Controller';
				}
				$path .= $value . "\\";
			}
			
			if (! empty($route[1])) {
				
				$_REQUEST = array_merge($route[1], $_REQUEST);
				$request->setBody($_REQUEST);
			}
			return [
				lcfirst($pathArr[0]),
				rtrim($path, "\\") . 'Controller',
				$action
			];
		} else {
			// 走默认路由器
			return (PathParseRouter::Instance()->getRoute($request));
		}
	}

	public function parseRequest ($request) {
		
		/**
		 *
		 * @var $rule RegexRouter
		 */
		foreach ($this->rules as $rule) {
			
			if (($result = $rule->parseRequest($this, $request)) !== false) {
				return $result;
			}
		}
		return null;
	}
}

// 私有Request
class Request {
	// // 处理基本信息
	public function getUrl () {
		if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // IIS
			$requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
		} elseif (isset($_SERVER['REQUEST_URI'])) {
			$requestUri = $_SERVER['REQUEST_URI'];
			if ($requestUri !== '' && $requestUri[0] !== '/') {
				$requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $requestUri);
			}
		} elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0 CGI
			$requestUri = $_SERVER['ORIG_PATH_INFO'];
			if (! empty($_SERVER['QUERY_STRING'])) {
				$requestUri .= '?' . $_SERVER['QUERY_STRING'];
			}
		} else {
			throw new \Exception('Unable to determine the request URI.');
		}
		return $requestUri;
	}

	public function getScriptFile () {
		if (isset($_SERVER['SCRIPT_FILENAME'])) {
			return $_SERVER['SCRIPT_FILENAME'];
		} else {
			throw new \Exception();
		}
	}

	public function getScriptUrl () {
		$scriptFile = $this->getScriptFile();
		$scriptName = basename($scriptFile);
		if (isset($_SERVER['SCRIPT_NAME']) && basename($_SERVER['SCRIPT_NAME']) === $scriptName) {
			$scriptUrl = $_SERVER['SCRIPT_NAME'];
		} elseif (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) === $scriptName) {
			$scriptUrl = $_SERVER['PHP_SELF'];
		} elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName) {
			$scriptUrl = $_SERVER['ORIG_SCRIPT_NAME'];
		} elseif (isset($_SERVER['PHP_SELF']) && ($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptName)) !== false) {
			$scriptUrl = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $scriptName;
		} elseif (! empty($_SERVER['DOCUMENT_ROOT']) && strpos($scriptFile, $_SERVER['DOCUMENT_ROOT']) === 0) {
			$scriptUrl = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $scriptFile));
		} else {
			throw new \Exception('Unable to determine the entry script URL.');
		}
		
		return $scriptUrl;
	}

	public function getBaseUrl () {
		return rtrim(dirname($this->getScriptUrl()), '\\/');
	}

	public function getIsSecureConnection () {
		return isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] == 1) || isset(
				$_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
	}

	public function getSecurePort () {
		$securePort = $this->getIsSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 443;
		
		return $securePort;
	}

	public function getPort () {
		return ! $this->getIsSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 80;
	}

	public function getHostInfo () {
		$hostInfo = '';
		$secure = $this->getIsSecureConnection();
		$http = $secure ? 'https' : 'http';
		if (isset($_SERVER['HTTP_HOST'])) {
			$hostInfo = $http . '://' . $_SERVER['HTTP_HOST'];
		} elseif (isset($_SERVER['SERVER_NAME'])) {
			$hostInfo = $http . '://' . $_SERVER['SERVER_NAME'];
			$port = $secure ? $this->getSecurePort() : $this->getPort();
			if (($port !== 80 && ! $secure) || ($port !== 443 && $secure)) {
				$hostInfo .= ':' . $port;
			}
		}
		return $hostInfo;
	}

	public function getMethod () {
		if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
			return strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
		}
		
		if (isset($_SERVER['REQUEST_METHOD'])) {
			return strtoupper($_SERVER['REQUEST_METHOD']);
		}
		
		return 'GET';
	}

	public function getPathInfo () {
		$pathInfo = $this->getUrl();
		if (($pos = strpos($pathInfo, '?')) !== false) {
			$pathInfo = substr($pathInfo, 0, $pos);
		}
		$pathInfo = urldecode($pathInfo);
		// try to encode in UTF8 if not so
		// http://w3.org/International/questions/qa-forms-utf-8.html
		if (! preg_match(
				'%^(?:
            [\x09\x0A\x0D\x20-\x7E]              # ASCII
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
            | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
            | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
            )*$%xs', $pathInfo)) {
			$pathInfo = utf8_encode($pathInfo);
		}
		$scriptUrl = $this->getScriptUrl();
		$baseUrl = $this->getBaseUrl();
		if (strpos($pathInfo, $scriptUrl) === 0) {
			$pathInfo = substr($pathInfo, strlen($scriptUrl));
		} elseif ($baseUrl === '' || strpos($pathInfo, $baseUrl) === 0) {
			$pathInfo = substr($pathInfo, strlen($baseUrl));
		} elseif (isset($_SERVER['PHP_SELF']) && strpos($_SERVER['PHP_SELF'], $scriptUrl) === 0) {
			$pathInfo = substr($_SERVER['PHP_SELF'], strlen($scriptUrl));
		} else {
			throw new \Exception('Unable to determine the path info of the current request.');
		}
		if (substr($pathInfo, 0, 1) === '/') {
			$pathInfo = substr($pathInfo, 1);
		}
		return (string) $pathInfo;
	}
}