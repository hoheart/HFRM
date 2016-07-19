<?php

namespace Framework\Http;

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
	 *
	 * @var \EvIo $mEv
	 */
	protected $mEv = null;
	
	/**
	 * 等待响应的数量
	 *
	 * @var int
	 */
	protected static $WaitedCount = 0;

	static public function waitUntilAllResponded () {
		\Ev::run();
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
		$fn = function  ($w, $r) use( $srcfn) {
			$resp = null;
			$respStr = '';
			while (! feof($this->mConnection)) {
				$respStr .= fread($this->mConnection, 8192);
				$pos = strpos($respStr, "\r\n\r\n");
				if (false !== $pos) {
					$resp = new HttpResponse($respStr, $pos);
					$contentLen = $resp->getContentLength();
					
					$readLen = strlen($respStr) - $pos - 4;
					$unreadLen = $contentLen - $readLen;
					if ($unreadLen > 0) {
						$str = fread($this->mConnection, $unreadLen);
						$resp->addBody($str);
						
						break;
					}
				}
			}
			
			-- self::$WaitedCount;
			if (0 === self::$WaitedCount) {
				\Ev::stop();
			}
			
			$srcfn($resp);
		};
		$this->mEv = new \EvIo($this->mConnection, \Ev::READ, $fn);
	}
}