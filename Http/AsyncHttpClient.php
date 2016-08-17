<?php
namespace Framework\Http;

use Framework\Http\HttpRequest;
use Framework\Exception\NetworkErrorException;
use Framework\Http\HttpResponse;

class AsyncHttpClient
{

    /**
     * 单位：秒
     *
     * @var int
     */
    const RESPONSE_TIMEOUT = 30;

    /**
     *
     * @var resource $mConnection
     */
    protected $mConnection = false;

    /**
     *
     * @var \EvIo $mReadEv 读事件
     */
    protected $mReadEv = null;

    /**
     *
     * @var \EvIo $mWriteEv
     */
    protected $mWriteEv = null;

    /**
     *
     * @var \EvTimer $mTimeoutEv
     */
    protected $mTimeoutEv = null;

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
    protected $mRequestedCount = 0;

    /**
     *
     * @var \Closure $mCallback
     */
    protected $mCallback = null;

    public static function waitUntilAllResponded()
    {
        \Ev::run();
    }

    protected function clearForNewRead()
    {
        $this->mResponse = null;
        $this->mReadBuf = '';
        $this->mChunkSize = - 1;
    }

    protected function clearForNewWrite()
    {
        $this->mWriteBuf = '';
        $this->mWroteLen = 0;
        $this->mRequestedCount = 0;
    }

    /**
     * 连接可写时调用
     *
     * @param \EvWatcher $ew
     *            Ev的事件对象
     * @param int $events
     *            Ev的事件类型
     * @throws NetworkErrorException
     */
    public function onWrite($ew, $events)
    {
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
        
        if ($this->mWroteLen == strlen($this->mWriteBuf)) {
            // 一个请求发送完毕，应该等待接收新的响应
            $this->clearForNewRead();
            
            if (null == $this->mTimeoutEv) {
                $this->mTimeoutEv = new \EvTimer(30, 1, array(
                    $this,
                    'onTimeout'
                ));
            } else {
                $this->mTimeoutEv->again();
            }
            
            ++ $this->mRequestedCount;
            
            ++ self::$WaitedCount;
        }
    }

    /**
     * 超时时执行
     *
     * @param \EvWatcher $ew
     *            Ev的事件对象
     * @param int $events
     *            Ev的事件类型
     */
    public function onTimeout($ew, $events)
    {
        $fn = $this->mCallback;
        $fn($this->mResponse);
        
        $this->clearForNewRead();
    }

    /**
     * 有数需要读取时调用。
     *
     * @param \EvWatcher $ew
     *            Ev的事件对象
     * @param int $events
     *            Ev的事件类型
     * @throws NetworkErrorException
     */
    public function onRead($ew, $events)
    {
        $meta = stream_get_meta_data($this->mConnection);
        $uri = $meta['uri'];
        
        // 8192一般是一个tcp包的大小
        // false表示网络出错了，空表示连接已经关闭，这个时候都调用close释放资源
        $ret = fread($this->mConnection, 8192);
        if (false === $ret || '' === $ret) {
            fclose($this->mConnection);
            $this->mConnection = false;
            
            throw new NetworkErrorException('connection closed or error. uri:' . $uri, 'framework');
        }
        
        $fn = $ew->data;
        
        $this->parseResponseData($ret, $fn);
    }

    protected function parseResponseData($str, $fn)
    {
        if ('' === $str) {
            return;
        }
        
        $readComplete = false;
        
        $meta = stream_get_meta_data($this->mConnection);
        $uri = $meta['uri'];
        
        if (null == $this->mResponse) {
            // 如果mReadBuf里之前有两个回车换行，肯定就有mResponse对象了。如果没有，就只从当前的str里找就ok了
            $pos = strpos($str, "\r\n\r\n");
            if (false !== $pos) {
                try {
                    $sub = substr($str, 0, $pos);
                    $this->mResponse = HttpResponse::parse($this->mReadBuf . $sub);
                } catch (\Exception $e) {
                    // 响应是错误的。
                    throw new NetworkErrorException('http header error : ' . $e->getMessage() . '. uri:');
                }
                
                // 头信息解析完毕，剩下的是body信息
                $str = substr($str, $pos + 4);
            } else {
                $this->mReadBuf = $str;
                $str = '';
            }
        }
        
        // 上面解析了头，下面就是body了
        $transferEncoding = $this->mResponse->getHeader(HttpResponse::HEADER_TRANSFER_ENCODING);
        if (HttpResponse::TRANSFER_ENCODING_CHUNKED != $transferEncoding) {
            $readComplete = $this->parseUnchunked($str);
        } else {
            $readComplete = $this->parseChunked($str);
        }
        
        // 读完了整个响应包，就该调用回调函数了。
        if ($readComplete) {
            $fn($this->mResponse);
            
            -- self::$WaitedCount;
            
            if (0 === self::$WaitedCount) {
                \Ev::stop();
            }
            
            -- $this->mRequestedCount;
            
            // 一次解析完毕后，如果还有请求，接着解析收到的数据，否则丢弃数据
            $str = $this->mReadBuf;
            $this->mReadBuf = '';
            if ($this->mRequestedCount > 0) {
                $this->parseResponseData($str, $fn);
            }
        }
    }

    protected function parseUnchunked($str)
    {
        $this->mReadBuf .= $str;
        
        $bodyLen = strlen($this->mReadBuf);
        $contentLen = $this->mResponse->getHeader(HttpRequest::HEADER_CONTENT_LENGTH);
        if ($contentLen < $bodyLen) {
            $body = substr($this->mReadBuf, 0, $contentLen);
            $this->mResponse->setBody($body);
            
            $this->mReadBuf = substr($this->mReadBuf, $contentLen);
        } elseif ($contentLen == $bodyLen) {
            $this->mResponse->setBody($this->mBodyBuf);
        } else {
            return false;
        }
        
        return true;
    }

    protected function parseChunked($str)
    {
        if ('' === $str) {
            return false;
        }
        
        // 如果还没有解析到chunk头
        if (- 1 == $this->mChunkSize) {
            $pos = strpos($str, "\r\n");
            if (false !== $pos) {
                $sub = $this->mReadBuf . substr($str, 0, $pos);
                $this->mChunkSize = (integer) hexdec($sub);
                
                $this->mReadBuf = '';
                
                $str = substr($str, $pos + 2);
            } else {
                $this->mReadBuf .= $str;
            }
        }
        
        if (- 1 !== $this->mChunkSize) {
            $this->mReadBuf .= $str;
            
            if (strlen($this->mReadBuf) >= $this->mChunkSize) {
                $chunk = substr($this->mReadBuf, 0, $this->mChunkSize);
                $this->mResponse->addBody($chunk);
                
                $str = substr($this->mReadBuf, $this->mChunkSize);
                
                // 一个chunk解析完毕，变量置-1，以解析下一个
                $this->mChunkSize = - 1;
                $this->mReadBuf = '';
                
                $this->parseChunked($str);
            } // else {//等着读该chunk的后续内容}
        } else {
            if (0 === $this->mChunkSize) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 根据请求指定的地址，简历与服务器的连接，但并不发起请求。如果连接已经建立，就不在重新建立。
     *
     * @param HttpRequest $req
     *            请求对象
     * @param \Closure $srcFn
     *            回调函数
     * @throws NetworkErrorException
     */
    public function connect(HttpRequest $req, $srcFn)
    {
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
            
            if (null != $srcFn) {
                if (null == $this->mReadEv) {
                    $this->mReadEv = new \EvIo($this->mConnection, \Ev::READ, array(
                        $this,
                        'onRead'
                    ));
                } else {
                    $this->mReadEv->set($this->mConnection, \Ev::READ);
                }
            }
            
            if (null == $this->mWriteEv) {
                $this->mWriteEv = new \EvIo($this->mConnection, \Ev::WRITE, array(
                    $this,
                    'onWrite'
                ));
            } else {
                $this->mWriteEv->set($this->mConnection, \Ev::WRITE);
            }
        }
    }

    /**
     * 发起post请求
     *
     * @param string $url
     *            url
     * @param string $data
     *            data
     * @param \Closure $srcFn
     *            回调函数
     */
    public function post($url, $data = '', \Closure $srcFn = null)
    {
        $req = new HttpRequest($url);
        $req->setMethod('POST');
        $req->setBody($data);
        
        $this->request($req, $srcFn);
    }

    /**
     * 发起请求
     *
     * @param HttpRequest $req
     *            请求对象
     * @param \Closure $srcFn
     *            回调函数
     */
    public function request(HttpRequest $req, \Closure $srcFn = null)
    {
        $this->connect($req, $srcFn);
        
        $this->clearForNewWrite();
        
        $this->mWriteBuf = $req->pack();
        
        if (null != $srcFn) {
            $this->mCallback = $srcFn;
        }
        
        $this->onWrite(null, null);
    }
}
