<?php

namespace Framework\Request;

class CliRequest implements IRequest {
	
	/**
	 * 参数列表
	 *
	 * @var array
	 */
	protected $mKeyVal = array();

	public function __construct () {
		global $argc, $argv;
		
		for ($i = 2; $i < $argc; ++ $i) {
			$tmp = explode('=', $argv[$i]);
			$this->mKeyVal[$tmp[0]] = $tmp[1];
		}
	}

	public function isHttp () {
		return false;
	}

	public function isCli () {
		return true;
	}

	public function getResource () {
		global $argv;
		
		return $argv[1];
	}

	public function get ($key) {
		return $this->mKeyVal[$key];
	}

	public function getAllParams () {
		return $this->mKeyVal;
	}

	public function getMethod () {
		// 命令行的请求类型都是get
		return self::REQUEST_METHOD_GET;
	}
}