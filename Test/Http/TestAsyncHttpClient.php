<?php
namespace Test\Http;

use Framework\Http\AsyncHttpClient;
use Framework\Http\HttpRequest;

/**
 * 对异步HttpClient进行测试，需要搭建一个http服务器，把收到的所有数据（包括http command line , header）原样发回(以http协议)。
 *
 * @author Hoheart
 */
class TestAsyncHttpClient extends \PHPUnit_Framework_TestCase
{

    /**
     * 测试发送是否成功。
     * 因为异步发送的，所以要测长点的和短点的数据
     */
    public function testRequest()
    {
        $host = '127.0.0.1';
        $port = 60000;
        
        $req = new HttpRequest("http://$host:$port/abc");
        $req->setMethod('POST');
        $body = '01234';
        $req->addBody($body);
        
        $packedData = $req->pack();
        
        // 先测试短点的数据
        $client = new AsyncHttpClient();
        $resp = null;
        $client->request($req, function ($r) use ($resp) {
            $resp = $r;
        }, 1, 1);
        
        AsyncHttpClient::waitUntilAllResponded();
        
        if (null == $resp || $packedData != $resp->getBody()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }
}
