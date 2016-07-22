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
	
	/**
	 *
	 * @var string $mReadBuf
	 */
	protected $mReadBuf = '';
	
	/**
	 *
	 * @var string
	 */
	protected $mWriteBuf = '';
	
	/**
	 *
	 * @var int $mWritePos
	 */
	protected $mWritePos = 0;
	
	/**
	 *
	 * @var HttpResponse $mResp
	 */
	protected $mResponse = null;

	static public function waitUntilAllResponded () {
		\Ev::run();
	}

	public function onWrite ($ew, $events) {
		if ($this->mWritePos != strlen($this->mWriteBuf)) {
			// 没有新的数据需要写入。
			return;
		}
		
		$remain = substr($this->mWriteBuf, $this->mWritePos);
		$ret = fwrite($this->mConnection, $remain);
		if (false === $ret) {
			fclose($this->mConnection);
			$this->mConnection = false;
			
			Log::r('connection error.', 'framework');
			
			return;
		}
		
		$this->mWritePos = $ret;
		
		if ($this->mWritePos != strlen($this->mWriteBuf)) {
			// 一个请求发送完毕，应该等待接收新的响应
			$this->mResponse = null;
			
			++ self::$WaitedCount;
		}
	}

	public function onRead ($ew, $events) {
		$srcfn = $ew->data;
		
		$ret = fread($this->mConnection, 8192);
		if ('' === $ret || false === $ret) {
			fclose($this->mConnection);
			$this->mConnection = false;
			
			$srcfn($resp);
			
			if ('' === $ret) {
				Log::r('connection closed by server', 'framework');
			} else if (false === $ret) {
				Log::r('connection error.', 'framework');
			}
			
			return;
		}
		
		if (null == $this->mResponse) {
			// 说明还没有接收完头
			$pos = strpos($ret, "\r\n\r\n");
			if (false === $pos) {
				$this->mReadBuf .= $ret;
			} else {
			}
		}
		
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

	protected function connect (HttpRequest $req, $srcFn) {
		if (false === $this->mConnection) {
			list ($host, $port) = explode(':', $req->getHeader('Host'));
			if (empty($port)) {
				$port = 80;
			}
			
			$this->mConnection = fsockopen($host, $port, $errno, $errstr, 1);
			if (false === $this->mConnection) {
				Log::r('can not connect ' . $req->getHeader('Host'), 'rpc');
				
				throw new RPCServiceErrorException();
			}
			
			stream_set_blocking($this->mConnection, false);
			
			$this->mEv = new \EvIo($this->mConnection, \Ev::READ, array(
				$this,
				'onRead'
			), $srcFn);
			
			$this->mEv = new \EvIo($this->mConnection, \Ev::WRITE, array(
				$this,
				'onWrite'
			));
		}
	}

	public function post ($url, $dataMap = array(), \Closure $srcFn) {
		$req = new HttpRequest($url);
		$req->setMethod('POST');
		$req->setBodyMap($dataMap);
		
		$this->request($req, $srcFn);
	}

	public function request (HttpRequest $req, \Closure $srcFn) {
		$this->connect($req, $srcFn);
		
		$this->mWriteBuf = $req->pack();
		$this->mWritePos = 0;
		
		$this->onWrite(null, null);
	}
}