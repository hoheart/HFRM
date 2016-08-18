<?php
namespace Test\Http;

use Framework\Http\AsyncHttpClient;
use Framework\Http\HttpResponse;

/**
 * 对异步HttpClient进行测试，需要搭建一个http服务器，把收到的所有数据（包括http command line , header）原样发回(以http协议)。
 *
 * @author Hoheart
 *        
 */
class TestAsyncHttpClient extends \PHPUnit_Framework_TestCase
{

    /**
     * 测试发送是否成功。
     * 因为异步发送的，所以要测长点的和短点的数据
     */
    public function testRequest()
    {
        $host = '172.16.20.58';
        $port = 50000;
        // 先测试短点的数据
        $client = new AsyncHttpClient();
        $body = '01234';
        $resp = null;
        $client->post("http://$host:$port/abc", $body, function (HttpResponse $r) use($resp) {
            echo 6666;
            exit();
            $resp = $r;
        }, 1, 1);
        
        AsyncHttpClient::waitUntilAllResponded();
        
        $reqStr = 'POST /abc HTTP/1.1' . "\r\n";
        $reqStr .= 'Host: 127.0.0.1' . "$port\r\n";
        $reqStr .= 'Content-Length: ' . strlen($body) . "\r\n";
        $reqStr .= "\r\n";
        if ($reqStr != $resp->getBody()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }
}
