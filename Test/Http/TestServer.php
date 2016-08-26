#!/usr/bin/env php
<?php
namespace Test\Http;

use Framework\Net\TCPServer;
use Framework\Net\Connection;
use Framework\App;
use Framework\Net\IConnectionManager;

require_once(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'App.php');

// 这样就有自动加载类了，就可以加载Server类了。
$app = App::Instance();
$app->registerAutoloader();

class TestServer implements IConnectionManager
{

    protected $mReadFn = null;

    public function __construct()
    {
        $this->mReadFn = function ($data, Connection $c) {
            $this->onRequest($data, $c);
        };
    }

    public function main()
    {
        $s = new TCPServer('0.0.0.0', 60000);
        $s->on(TCPServer::EVENT_CONNECT, $this);
        
        $s->start();
    }

    public function onConnect(Connection $c)
    {
        $c->read($this->mReadFn);
    }

    public function onRequest($data, $c)
    {
        $pos = strpos($data, "\r\n\r\n");
        if (false !== $pos) {
            $body = substr($data, $pos + 4);
            if (false === $body) {
                $body = '';
            }
            
            $c->send($body);
        }
        
        $c->read($this->mReadFn);
    }
}

$s = new TestServer();
$s->main();
