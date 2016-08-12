<?php
namespace Framework\Net;

class Connection
{

    protected $mSock = null;

    protected $mReadEv = null;

    protected $mWriteEv = null;

    public function __construct($sock)
    {
        $this->mSock = $sock;
        
        stream_set_blocking($sock, false);
        
        $this->mReadEv = new \EvIo($this->mSock, Ev::READ, array(
            $this,
            'onAccept'
        ));
    }
}