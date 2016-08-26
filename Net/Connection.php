<?php
namespace Framework\Net;

use Framework\Exception\NetworkErrorException;
use Framework\HFC\Exception\ParameterErrorException;

class Connection
{

    /**
     * 缓存大小,10M
     *
     * @var int
     */
    const BUFFER_SIZE = 10485760;

    protected $mSock = null;

    protected $mReadEv = null;

    protected $mWriteEv = null;

    protected $mReadBuf = '';

    protected $mWriteBuf = '';

    protected $mWaitReadLen = 0;

    protected $mWaitReadFn = null;

    public function __construct($sock)
    {
        $this->mSock = $sock;
        
        stream_set_blocking($sock, false);
        
        $this->mReadEv = new \EvIo($this->mSock, \Ev::READ, array(
            $this,
            'onRead'
        ));
        
        $this->mWriteEv = new \EvIo($this->mSock, \Ev::WRITE, array(
            $this,
            'onWrite'
        ));
    }

    public function onWrite($ew, $events)
    {
        if ('' === $this->mWriteBuf) {
            // 没有数据写，会不停的通知。
            if (null != $ew) {
                $this->mWriteEv->stop();
            }
            
            return;
        }
        
        $len = fwrite($this->mSock, $this->mWriteBuf);
        if (false === $len) {
            fclose($this->mSock);
            $this->mSock = false;
            
            throw new NetworkErrorException('connection closed or error.');
        }
        
        if (strlen($this->mWriteBuf) > $len) {
            $this->mWriteBuf = substr($this->mWriteBuf, $len);
            
            $this->mWriteEv->start();
        } else {
            $this->mWriteBuf = '';
        }
    }

    public function onRead($ew, $events)
    {
        // 如果没有人读，缓存不能超过最大值
        if (null == $this->mWaitReadFn) {
            if (strlen($this->mReadBuf) >= self::BUFFER_SIZE) {
                return;
            }
        }
        
        $ret = fread($this->mSock, 8192);
        if ('' === $ret) {
            fclose($this->mSock);
            $this->mSock = false;
            
            return;
        }
        
        $this->mReadBuf .= $ret;
        
        $this->readFromBuf();
    }

    protected function readFromBuf()
    {
        if ('' === $this->mReadBuf || null == $this->mWaitReadFn) {
            return;
        }
        
        $fn = $this->mWaitReadFn;
        $bufLen = strlen($this->mReadBuf);
        $waitLen = $this->mWaitReadLen;
        if (- 1 == $this->mWaitReadLen) {
            $waitLen = strlen($this->mReadBuf);
        }
        
        if ($waitLen <= $bufLen) {
            $content = substr($this->mReadBuf, 0, $waitLen);
            if ($waitLen >= $bufLen) {
                $this->mReadBuf = '';
            } else {
                $this->mReadBuf = substr($this->mReadBuf, $waitLen);
            }
            $this->mWaitReadFn = null;
            
            $fn($content, $this);
        }
    }

    public function read(\Closure $fn, $len = -1)
    {
        $this->mWaitReadFn = $fn;
        $this->mWaitReadLen = $len;
        
        $this->readFromBuf();
    }

    public function send($data)
    {
        if (! is_string($data)) {
            throw new ParameterErrorException();
        }
        
        if (0 == strlen($data)) {
            return;
        }
        
        $this->mWriteBuf = $data;
        
        $this->onWrite(null, null);
    }
}
