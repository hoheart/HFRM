<?php

namespace Framework\App;

use Framework\Http\HttpRequest;
use Framework\Facade\Log;
use Framework\Exception\RPCServiceErrorException;
use Framework\Http\HttpResponse;

class AsyncHttpClient {
	
	/**
	 *
	 * @var resource $mConnection
	 */
	protected $mConnection = false;
	
	/**
	 * 等待响应的数量
	 *
	 * @var int
	 */
	protected static $WaitedCount = 0;

	static public function waitUntilAllResponded () {
		while (0 !== self::WaitedCount) {
			\Ev::wait(\Ev::RUN_ONCE);
			-- self::$WaitedCount;
		}
	}

	public function post ($url, $dataMap = array(), \Closure $srcfn) {
		$req = new HttpRequest($url);
		$req->setMethod('POST');
		$req->setBodyMap($dataMap);
		
		if (false === $this->mConnection) {
			list ($host, $port) = explode(':', $req->getHeader('Host'));
			if (empty($port)) {
				$port = 80;
			}
			
			$this->mConnection = fsockopen($host, $port, $errno, $errstr, 1);
			if (false === $this->mConnection) {
				Log::r('can not connect ' . $url, 'rpc');
				
				throw new RPCServiceErrorException();
			}
			
			stream_set_blocking($this->mConnection, false);
		}
		
		$reqStr = $req->pack();
		
		++ self::$WaitedCount;
		
		fwrite($this->mConnection, $reqStr);
		$fn = function  ($w) use( $srcfn) {
			$respStr = '';
			while (! feof($this->mConnection)) {
				$respStr .= fread($this->mConnection, 8192);
			}
			
			$resp = new HttpResponse($respStr);
			$srcfn($resp);
		};
		new \EvIo($this->mConnection, \Ev::READ, $fn);
	}
}