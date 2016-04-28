<?php

namespace Framework\Output;

use Framework\Response\IResponse;

/**
 * php默认的输出，及用echo可以打印出的输出
 *
 * @author Hoheart
 *        
 */
class StandardOutputStream implements IOutputStream {

	public function write ($str, $offset = 0, $count = -1) {
		$subStr = substr($str, $offset, $count);
		echo $subStr;
	}

	public function flush () {
		ob_flush();
		flush();
	}

	public function close () {
	}

	public function output (IResponse $resp) {
		http_response_code($resp->getStatus());
		
		foreach ($resp->getAllHeader() as $key => $val) {
			if (is_array($val)) {
				foreach ($val as $one) {
					header($key . ': ' . $one, true);
				}
			} else {
				header($key . ': ' . $val);
			}
		}
		
		foreach ($resp->getAllCookie() as $key => $cookie) {
			setcookie($key, $cookie['value'], $cookie['expire'], $cookie['path'], $cookie['domain'], $cookie['secure'], 
					$cookie['httponly']);
		}
		
		echo $resp->getBody();
	}
}