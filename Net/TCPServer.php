<?php
namespace Framework\Test\Http;

use Framework\HFC\Exception\SystemAPIErrorException;

class TCPServer
{

    protected $mHost = '';

    protected $mPort = 0;

    protected $mSock = null;
    
    protected $mConnectionManager = null;

    public function __construct($ip, $port)
    {
        $this->mHost = $ip;
        $this->mPort = $port;
    }

    public function on($event, IConnectionManager $cm)
    {
        switch ($event) {
            case 'connection':
                
                break;
        }
    }

    public function start()
    {
        $ipStr = 'tcp://' . $this->mHost . ':' . $this->mPort;
        $sock = stream_socket_server($ipStr, $errno, $errstr);
        if (false === $sock) {
            throw new SystemAPIErrorException($errstr);
        }
        
        stream_set_blocking($sock, false);
    }
}