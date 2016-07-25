<?php

namespace Framework\Http;

use Framework\Http\HttpRequest;
use Framework\Exception\NetworkErrorException;
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
	 * @var int $mWroteLen
	 */
	protected $mWroteLen = 0;
	
	/**
	 *
	 * @var HttpResponse $mResp
	 */
	protected $mResponse = null;
	
	/**
	 * 当前解析的chunk大小
	 *
	 * @var int $mChunkSize
	 */
	protected $mChunkSize = - 1;
	
	/**
	 * 发出的请求数
	 *
	 * @var int $mRequestCount
	 */
	protected $mRequestCount = 0;

	static public function waitUntilAllResponded () {
		\Ev::run();
	}

	protected function clearForNewRead () {
		$this->mResponse = null;
		$this->mHeadBuf = '';
		$this->mBodyBuf = '';
		$this->mChunkSize = - 1;
		$this->mChunkHeaderBuf = '';
	}

	protected function clearForNewWrite () {
		$this->mWriteBuf = '';
		$this->mWroteLen = 0;
	}

	public function onWrite ($ew, $events) {
		// 没有新的数据需要写入。
		if ($this->mWroteLen == strlen($this->mWriteBuf)) {
			return;
		}
		
		$remain = substr($this->mWriteBuf, $this->mWroteLen);
		$ret = fwrite($this->mConnection, $remain);
		if (false === $ret || 0 === $ret) {
			fclose($this->mConnection);
			$this->mConnection = false;
			
			$ret = stream_get_meta_data($this->mConnection);
			$uri = $ret['uri'];
			
			throw new NetworkErrorException('connection closed or error. uri:' . $uri);
		}
		
		$this->mWroteLen += $ret;
		
		if ($this->mWroteLen != strlen($this->mWriteBuf)) {
			// 一个请求发送完毕，应该等待接收新的响应
			$this->clearForNewRead();
			
			++ $this->mRequestCount;
			
			++ self::$WaitedCount;
		}
	}

	public function onRead ($ew, $events) {
		$readComplete = false;
		
		$ret = stream_get_meta_data($this->mConnection);
		$uri = $ret['uri'];
		
		// 8192一般是一个tcp包的大小
		// false表示网络出错了，空表示连接已经关闭，这个时候都调用close释放资源
		$ret = fread($this->mConnection, 8192);
		if (false === $ret || '' === $ret) {
			throw new NetworkErrorException('connection closed or error. uri:' . $uri, 'framework');
			
			fclose($this->mConnection);
			$this->mConnection = false;
		} else {
			$this->mReadBuf .= $ret;
			
			if (null == $this->mResponse) {
				// 如果mReadBuf里之前有两个回车换行，肯定就有mResponse对象了。如果没有，就只从当前的ret里找就ok了
				$pos = strpos($ret, "\r\n\r\n");
				if (false !== $pos) {
					try {
						$this->mResponse = HttpResponse::parse(substr($this->mReadBuf, 0, $pos));
					} catch (\Exception $e) {
						// 响应是错误的。
						throw new NetworkErrorException(
								'http header error : ' . $e->getMessage() . '. uri:');
					}
					
					// 头信息解析完毕，缓存用于接受body信息
					$this->mReadBuf = substr($ret, $pos + 4);
				}
			} else {
				if ('chunked' == $this->mResponse->getHeader('Transfer-Encoding')) {
					$this->parseChunked();
				} else {
					$bodyLen = strlen($this->mReadBuf);
					$contentLen = $this->mResponse->getHeader(HttpRequest::HEADER_CONTENT_LENGTH);
					$body = $this->mReadBuf;
					if ($contentLen < $bodyLen) {
						$body = substr($this->mReadBuf, 0, $contentLen);
						
						$this->mReadBuf = substr($this->mReadBuf, $contentLen);
					}
					$this->mResponse->setBody($this->mBodyBuf);
					
					$readComplete = true;
				}
			}
		}
		
		// 读完了整个响应包，就该调用回调函数了。注意不要清空mReadBuf，因为可能已经读取下一个响应了。
		if ($readComplete) {
			$srcfn = $ew->data;
			$srcfn($this->mResponse);
			
			-- self::$WaitedCount;
			
			if (0 === self::$WaitedCount) {
				\Ev::stop();
			}
		}
	}

	/**
	 * 解析chunk信息，次函数为递归，每次只解析头或体
	 *
	 * @param string $str        	
	 */
	protected function parseChunked ($str) {
		if ('' === $str) {
			return;
		}
		
		// 如果还没有解析到chunk头
		if (- 1 == $this->mChunkSize) {
			$pos = strpos($str, "\r\n");
			if (false !== $pos) {
				$this->mChunkSize = (integer) hexdec(substr($str, 0, $pos));
				
				$this->mReadBuf = '';
				
				$this->parseChunked(substr($str, $pos + 2));
			} else {
				$this->mReadBuf .= $str;
			}
		} else {
			// 解析chunk体
			$chunk = substr($str, 0, $this->mChunkSize);
			$this->mReadBuf .= $chunk;
			
			$pos = strlen($chunk);
			if ($pos >= $this->mChunkSize) {
				// 一个chunk解析完毕，变量置-1，以解析下一个
				$this->mChunkSize = - 1;
				$this->mReadBuf = '';
			}
			
			$this->parseChunked(substr($str, $pos));
		}
	}

	function connect (HttpRequest $req, $srcFn) {
		if (false === $this->mConnection) {
			list ($host, $port) = explode(':', $req->getHeader('Host'));
			if (empty($port)) {
				$port = 80;
			}
			
			$this->mConnection = fsockopen($host, $port, $errno, $errstr, 1);
			if (false === $this->mConnection) {
				throw new NetworkErrorException('can not connect ' . $req->getHeader('Host'));
			}
			
			stream_set_blocking($this->mConnection, false);
			
			$this->clearForNewRead();
			$this->clearForNewWrite();
			
			$this->mEv = new \EvIo($this->mConnection, \Ev::READ, 
					array(
						$this,
						'onRead'
					), $srcFn);
			
			$this->mEv = new \EvIo($this->mConnection, \Ev::WRITE, 
					array(
						$this,
						'onWrite'
					));
		}
	}

	public function post ($url, $data = '', \Closure $srcFn) {
		$req = new HttpRequest($url);
		$req->setMethod('POST');
		$req->setBody($data);
		
		$this->request($req, $srcFn);
	}

	public function request (HttpRequest $req, \Closure $srcFn) {
		$this->connect($req, $srcFn);
		
		$this->clearForNewWrite();
		
		$this->mWriteBuf = $req->pack();
		
		$this->onWrite(null, null);
	}
}