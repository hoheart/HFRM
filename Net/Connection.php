<?php
namespace Framework\Net;

class Connection
{

    protected $mSock = null;

    protected $mReadEv = null;

    protected $mWriteEv = null;

    protected $mReadBuf = '';

    protected $mWaitReadLen = 0;

    protected $mWaitReadFn = null;

    public function __construct($sock)
    {
        $this->mSock = $sock;
        
        stream_set_blocking($sock, false);
        
        $this->mReadEv = new \EvIo($this->mSock, Ev::READ, array(
            $this,
            'onRead'
        ));
    }

    public function onRead($ew, $events)
    {
        $ret = fread($this->mSock, 8192);
        $this->mReadBuf .= $ret;
        
        $this->readFromBuf();
    }

    protected function readFromBuf()
    {
        if (null != $this->mWaitReadFn) {
            $fn = $this->mWaitReadFn;
            
            if (- 1 == $this->mWaitReadLen) {
                $fn($this->mReadBuf);
            } else {
                $bufLen = strlen($this->mReadBuf);
                if ($this->mWaitReadLen <= $bufLen) {
                    $content = substr($this->mReadBuf, 0, $this->mWaitReadLen);
                    $this->mReadBuf = substr($this->mReadBuf, $this->mWaitReadLen);
                    
                    $this->mWaitReadFn = null;
                }
            }
        }
    }

    public function read(\Closure $fn, $len = -1)
    {
        $this->mWaitReadFn = $fn;
        $this->mWaitReadLen = $len;
        
        $this->readFromBuf();
    }
}