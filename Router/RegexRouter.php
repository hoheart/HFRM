<?php

/**
 * Created by PhpStorm.
 * User: HeYanLong
 * Date: 2016/3/1
 * Time: 13:54
 */
namespace Framework\Router;

use Framework\Config;

class RegexRouter implements IRegexRouter {
	
	/**
	 * 工作模式
	 */
	const PARSING_ONLY = 1;
	/**
	 * 工作模式
	 */
	const CREATION_ONLY = 2;
	
	/**
	 *
	 * @var integer 工作模式
	 *      1:只负责格式化请求
	 *      2:只负责创建url
	 *      不设置或0:则既要创建也要格式化
	 */
	public $mode;
	
	/**
	 *
	 * @var string|array 请求模式
	 */
	public $verb;
	public $suffix = '';
	public $route;
	public $pattern;
	public $host;
	protected $placeholders = [];
	protected $defaults = [];
	private $_routeParams = [];
	private $_paramRules = [];
	private $_routeRule;

	public function __construct ($pattern, $route, $verb, $mode) {
		if ($pattern === null) {
			throw new \Exception('UrlRule::pattern must be set.');
		}
		$this->pattern = $pattern;
		if ($route === null) {
			throw new \Exception('UrlRule::route must be set.');
		}
		$this->suffix = Config::Instance()->get('rules.suffix');
		$this->route = $route;
		if ($verb !== null) {
			if (is_array($this->verb)) {
				foreach ($this->verb as $i => $verb) {
					$this->verb[$i] = strtoupper($verb);
				}
			} else {
				$this->verb = [
					strtoupper($this->verb)
				];
			}
		}
		
		$this->pattern = trim($this->pattern, '/');
		$this->route = trim($this->route, '/');
		
		if ($this->host !== null) {
			$this->host = rtrim($this->host, '/');
			$this->pattern = rtrim($this->host . '/' . $this->pattern, '/');
		} elseif ($this->pattern === '') {
			$this->_template = '';
			$this->pattern = '#^$#u';
			
			return;
		} elseif (($pos = strpos($this->pattern, '://')) !== false) {
			if (($pos2 = strpos($this->pattern, '/', $pos + 3)) !== false) {
				$this->host = substr($this->pattern, 0, $pos2);
			} else {
				$this->host = $this->pattern;
			}
		} else {
			$this->pattern = '/' . $this->pattern . '/';
		}
		
		if (strpos($this->route, '<') !== false && preg_match_all('/<([\w._-]+)>/', $this->route, $matches)) {
			foreach ($matches[1] as $name) {
				$this->_routeParams[$name] = "<$name>";
			}
		}
		
		$tr = [
			'.' => '\\.',
			'*' => '\\*',
			'$' => '\\$',
			'[' => '\\[',
			']' => '\\]',
			'(' => '\\(',
			')' => '\\)'
		];
		
		$tr2 = [];
		if (preg_match_all('/<([\w._-]+):?([^>]+)?>/', $this->pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$name = $match[1][0];
				$pattern = isset($match[2][0]) ? $match[2][0] : '[^\/]+';
				$placeholder = 'a' . hash('crc32b', $name); // placeholder must
				                                            // begin with a letter
				$this->placeholders[$placeholder] = $name;
				if (array_key_exists($name, $this->defaults)) {
					$length = strlen($match[0][0]);
					$offset = $match[0][1];
					if ($offset > 1 && $this->pattern[$offset - 1] === '/' &&
							 (! isset($this->pattern[$offset + $length]) || $this->pattern[$offset + $length] === '/')) {
						$tr["/<$name>"] = "(/(?P<$placeholder>$pattern))?";
					} else {
						$tr["<$name>"] = "(?P<$placeholder>$pattern)?";
					}
				} else {
					$tr["<$name>"] = "(?P<$placeholder>$pattern)";
				}
				if (isset($this->_routeParams[$name])) {
					$tr2["<$name>"] = "(?P<$placeholder>$pattern)";
				} else {
					$this->_paramRules[$name] = $pattern === '[^\/]+' ? '' : "#^$pattern$#u";
				}
			}
		}
		
		$this->_template = preg_replace('/<([\w._-]+):?([^>]+)?>/', '<$1>', $this->pattern);
		$this->pattern = '#^' . trim(strtr($this->_template, $tr), '/') . '$#u';
		
		if (! empty($this->_routeParams)) {
			$this->_routeRule = '#^' . strtr($this->route, $tr2) . '$#u';
		}
	}

	/**
	 * 格式化请求
	 *
	 * @param
	 *        	$manager
	 * @param
	 *        	$request
	 * @return array|boolean
	 */
	public function parseRequest (RegexUrlManager $manager, Request $request) {
		if ($this->mode === self::CREATION_ONLY) {
			return false;
		}
		
		if (! empty($this->verb) && ! in_array($request->getMethod(), $this->verb, true)) {
			return false;
		}
		
		$pathInfo = $request->getUrl();
		$suffix = (string) ($this->suffix === null ? $manager->suffix : $this->suffix);
		
		if ($suffix !== '' && $pathInfo !== '') {
			$n = strlen($suffix);
			if (substr_compare($pathInfo, $suffix, - $n, $n) === 0) {
				$pathInfo = substr($pathInfo, 0, - $n);
				
				// if ($pathInfo === '') {
				// // suffix alone is not allowed
				// return false;
				// }
			} else {
				return false;
			}
		}
		
		if ($this->host !== null) {
			$pathInfo = strtolower($request->getHostInfo()) . ($pathInfo === '' ? '' : $pathInfo);
		}
		
		if (! preg_match($this->pattern, $pathInfo, $matches)) {
			return false;
		}
		$matches = $this->substitutePlaceholderNames($matches);
		
		foreach ($this->defaults as $name => $value) {
			if (! isset($matches[$name]) || $matches[$name] === '') {
				$matches[$name] = $value;
			}
		}
		$params = $this->defaults;
		$tr = [];
		foreach ($matches as $name => $value) {
			if (isset($this->_routeParams[$name])) {
				$tr[$this->_routeParams[$name]] = $value;
				unset($params[$name]);
			} elseif (isset($this->_paramRules[$name])) {
				$params[$name] = $value;
			}
		}
		if ($this->_routeRule !== null) {
			$route = strtr($this->route, $tr);
		} else {
			$route = $this->route;
		}
		
		return [
			$route,
			$params
		];
	}

	protected function substitutePlaceholderNames (array $matches) {
		foreach ($this->placeholders as $placeholder => $name) {
			if (isset($matches[$placeholder])) {
				$matches[$name] = $matches[$placeholder];
				unset($matches[$placeholder]);
			}
		}
		return $matches;
	}

	/**
	 * 创建url
	 *
	 * @param
	 *        	$manager
	 * @param
	 *        	$route
	 * @param
	 *        	$params
	 * @return string|boolean
	 */
	public function createUrl ($manager, $route, $params) {
		if ($this->mode === self::PARSING_ONLY) {
			return false;
		}
		$tr = [];
		
		// match the route part first
		if ($route !== $this->route) {
			if ($this->_routeRule !== null && preg_match($this->_routeRule, $route, $matches)) {
				$matches = $this->substitutePlaceholderNames($matches);
				foreach ($this->_routeParams as $name => $token) {
					if (isset($this->defaults[$name]) && strcmp($this->defaults[$name], $matches[$name]) === 0) {
						$tr[$token] = '';
					} else {
						$tr[$token] = $matches[$name];
					}
				}
			} else {
				return false;
			}
		}
		// match default params
		// if a default param is not in the route pattern, its value must also
		// be matched
		foreach ($this->defaults as $name => $value) {
			if (isset($this->_routeParams[$name])) {
				continue;
			}
			if (! isset($params[$name])) {
				return false;
			} elseif (strcmp($params[$name], $value) === 0) { // strcmp will do
			                                                  // string conversion
			                                                  // automatically
				unset($params[$name]);
				if (isset($this->_paramRules[$name])) {
					$tr["<$name>"] = '';
				}
			} elseif (! isset($this->_paramRules[$name])) {
				return false;
			}
		}
		// match params in the pattern
		foreach ($this->_paramRules as $name => $rule) {
			if (isset($params[$name]) && ! is_array($params[$name]) &&
					 ($rule === '' || preg_match($rule, $params[$name]))) {
				$tr["<$name>"] = true ? urlencode($params[$name]) : $params[$name];
				unset($params[$name]);
			} elseif (! isset($this->defaults[$name]) || isset($params[$name])) {
				return false;
			}
		}
		$url = trim(strtr($this->_template, $tr), '/');
		if ($this->host !== null) {
			$pos = strpos($url, '/', 8);
			if ($pos !== false) {
				$url = substr($url, 0, $pos) . preg_replace('#/+#', '/', substr($url, $pos));
			}
		} elseif (strpos($url, '//') !== false) {
			$url = preg_replace('#/+#', '/', $url);
		}
		if ($url !== '') {
			$url .= ($this->suffix === null ? $manager->suffix : $this->suffix);
		}
		if (! empty($params) && ($query = http_build_query($params)) !== '') {
			$url .= '?' . $query;
		}
		return $url;
	}
}