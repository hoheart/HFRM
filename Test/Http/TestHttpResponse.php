<?php
namespace Test\Http;

use Framework\Http\HttpResponse;

class TestHttpResponse extends \PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $this->testParse();
    }

    public function testParse()
    {
        $data = 'HTTP/1.1 208 OKK' . "\r\n";
        $data .= 'Content-Type: text/html' . "\r\n";
        $data .= 'Set-Cookie:a=%2F; expires=Thu, 01-Jan-1970 00:00:30 GMT; path=/a; domain=.a.com; secure; httponly' . "\r\n";
        $data .= 'Set-Cookie:b=c; expires=Thu, 01-Jan-1970 00:00:30 GMT; path=/a/b; domain=.name.com; secure' . "\r\n";
        $data .= 'Content-Length: 3' . "\r\n";
        $data .= "\r\n";
        $data .= '123';
        
        $resp = HttpResponse::parse($data);
        if ('HTTP/1.1' !== $resp->getVersion()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        if (208 !== $resp->getStatusCode()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        if ('OKK' !== $resp->getReasonPhrase()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        if ('text/html' !== $resp->getHeader('Content-Type')) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        if (3 !== $resp->getHeader('Content-Length')) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        if ('123' !== $resp->getBody()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        
        $allCookie = array(
            'a' => array(
                'value' => '/',
                'expires' => 30,
                'path' => '/a',
                'domain' => '.a.com',
                'secure' => true,
                'httponly' => true
            ),
            'b' => array(
                'value' => 'c',
                'expires' => 30,
                'path' => '/a/b',
                'domain' => '.name.com',
                'secure' => true
            )
        );
        if ($resp->getAllCookie() !== $allCookie) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }

    public function testOthers()
    {
        // 解析测完，其他的都已经包含了
    }
}
