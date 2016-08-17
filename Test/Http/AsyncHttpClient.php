<?php
namespace Test\Http;

use Framework\Net\TCPServer;
use Framework\Http\AsyncHttpClient;
use Framework\Net\IConnectionManager;
use Framework\Net\Connection;

class TestAsyncHttpClient extends \PHPUnit_Framework_TestCase implements IConnectionManager
{

    protected $mClient = null;

    public function onConnect(Connection $conn)
    {
        $this->mClient = $conn;
    }

    /**
     * 测试发送是否成功。
     * 因为异步发送的，所以要测长点的和短点的数据
     */
    public function request()
    {
        $port = 50000;
        $server = new TCPServer('0.0.0.0', $port);
        $server->on('accept', $this);
        
        // 先测试短点的数据
        $client = new AsyncHttpClient();
        $client->post('http://127.0.0.1/abc', '01234', function () {
            //
        });
    }
}
