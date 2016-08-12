<?php
namespace Framework\Net;

use Framework\HFC\Exception\SystemAPIErrorException;
use Framework\HFC\Exception\ParameterErrorException;

class TCPServer
{

    protected $mHost = '';

    protected $mPort = 0;

    protected $mSock = null;

    protected $mConnectionManager = null;

    protected $mReadEv = null;

    public function __construct($ip, $port)
    {
        $this->mHost = $ip;
        $this->mPort = $port;
    }

    public function on($event, IConnectionManager $cm)
    {
        switch ($event) {
            case 'connection':
                $this->mConnectionManager = $cm;
                
                break;
            default:
                throw new ParameterErrorException();
                
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
        
        $this->mReadEv = new \EvIo($this->mSock, Ev::READ, array(
            $this,
            'onAccept'
        ));
    }

    public function onAccept($watcher = null, $revents = null)
    {
        $sock = stream_socket_accept($this->mSock, 1);
        
        $conn = new Connection($sock);
        
        $this->mConnectionManager->onConnect($conn);
    }
}