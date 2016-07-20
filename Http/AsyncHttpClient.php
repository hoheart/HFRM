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

	public function onRead ($ew, $events) {
		$resp = null;
		$respStr = '';
		while (true) {
			$ret = stream_get_meta_data($this->mConnection);
			$ret = fread($this->mConnection, 8192);
			if (false === $ret || '' === $ret) {
				// 什么也没读到，说明连接已经关闭
				$this->mConnection = false;
				break;
			}
			
			if (null != $resp) {
				$resp->addBody($ret);
				
				if (strlen($resp->getBody()) >= $resp->getContentLength()) {
					break;
				}
			} else {
				$respStr .= $ret;
				$pos = strpos($respStr, "\r\n\r\n");
				if (false === $pos) {
					continue;
				} else {
					$resp = new HttpResponse($respStr, $pos);
				}
			}
		}
		
		-- self::$WaitedCount;
		if (0 === self::$WaitedCount) {
			\Ev::stop();
		}
		
		$srcfn = $ew->data;
		$srcfn($resp);
	}

	protected function connect ($req, $srcFn, $url = '') {
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
			
			$this->mEv = new \EvIo($this->mConnection, \Ev::READ, array(
				$this,
				'onRead'
			), $srcFn);
		}
	}

	public function post ($url, $dataMap = array(), \Closure $srcfn) {
		$req = new HttpRequest($url);
		$req->setMethod('POST');
		$req->setBodyMap($dataMap);
		
		$this->connect($req, $srcfn, $url);
		
		$reqStr = $req->pack();
		
		++ self::$WaitedCount;
		fwrite($this->mConnection, $reqStr);
	}
}