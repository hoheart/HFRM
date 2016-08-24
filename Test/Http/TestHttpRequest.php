<?php
namespace Test\Http;

use Framework\Http\HttpRequest;
use Framework\HFC\Exception\ParameterErrorException;
use Framework\Http\HttpMessage;

class TestHttpRequest extends \PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $this->testGetId();
        $this->testSetAndGetURI();
    }

    public function testSetAndGetURI()
    {
        try {
            new HttpRequest('http://asdf:adsf/asdf@asdfa/asdf');
        } catch (ParameterErrorException $e) {
            // 出现这个exception就对了
        } catch (\Exception $e) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        
        $host = '127.0.0.1';
        $port = 60000;
        $req = new HttpRequest("http://$host:$port/abc?name=%2f");
        if ("$host:$port" != $req->getHeader(HttpRequest::HEADER_HOST)) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        if ('/abc?name=%2f' != $req->getRequestURI()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }

    public function testGetId()
    {
        $req = new HttpRequest();
        $id = $req->getId();
        if (empty($id)) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }

    public function testSetAndGet()
    {
        $req = new HttpRequest();
        $req->set('name', 'hoheart');
        if ('hoheart' != $req->get('name')) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }

    public function testSetAndGetMethod()
    {
        $req = new HttpRequest();
        $req->setMethod('PUT');
        if ('PUT' != $req->getMethod()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }

    public function testIsAjax()
    {
        $req = new HttpRequest();
        $val = $req->setHeader('HTTP_X_REQUESTED_WITH', 'xmlhttpRequest');
        if (! $req->isAjaxRequest()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }

    public function testGetClientIp()
    {
        $req = new HttpRequest();
        $req->setHeader('HTTP_X_FORWARDED_FOR', '12');
        if ('12' != $req->getClientIP()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        
        $req = new HttpRequest();
        $req->setHeader('HTTP_CLIENT_IP', '1');
        if ('1' != $req->getClientIP()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        
        $req = new HttpRequest();
        $req->setHeader('REMOTE_ADDR', '2');
        if ('2' != $req->getClientIP()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        
        // 测试优先顺序
        $req->setHeader('HTTP_CLIENT_IP', '1');
        if ('1' != $req->getClientIP()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        
        $req->setHeader('HTTP_X_FORWARDED_FOR', '12');
        if ('12' != $req->getClientIP()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }

    public function testGetAllParams()
    {
        $req = new HttpRequest();
        
        $all = array(
            'a' => 'b',
            'ab' => 'cd',
            '34' => 45
        );
        foreach ($all as $key => $one) {
            $req->set($key, $one);
        }
        
        if ($all !== $req->getAllParams()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }

    public function testPack()
    {
        $host = '127.0.0.1';
        $port = 60000;
        
        try {
            new HttpRequest('http://asdf:adsf/asdf@asdfa/asdf');
        } catch (ParameterErrorException $e) {
            // 出现这个exception就对了
        } catch (\Exception $e) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        
        $req = new HttpRequest("http://$host:$port/abc?name=%2f");
        $req->setMethod('POST');
        $req->setCookie('id', '/', 30, '/a/b', '.che001.com', true, true);
        $req->setCookie('name', 'n', 30, '/a/b', '.che001.com', true, true);
        $body = '01234';
        $req->setBody($body);
        $req->set('a', 'b');
        $req->set('c', 'd');
        
        $packedData = $req->pack();
        
        $packedBody = '01234&a=b&c=d';
        $reqStr = 'POST /abc?name=%2f HTTP/1.1' . "\r\n";
        $reqStr .= 'Host: 127.0.0.1:' . "$port\r\n";
        $reqStr .= 'Content-Type: ' . HttpMessage::CONTENT_TYPE_URLENCODED . "\r\n";
        $reqStr .= 'Content-Length: ' . strlen($packedBody) . "\r\n";
        $reqStr .= 'Cookie: id=%2F; name=n' . "\r\n";
        $reqStr .= "\r\n";
        $reqStr .= $packedBody;
        if ($packedData !== $reqStr) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        
        $req->setBody('');
        $packedBody = 'a=b&c=d';
        $reqStr = 'POST /abc?name=%2f HTTP/1.1' . "\r\n";
        $reqStr .= 'Host: 127.0.0.1:' . "$port\r\n";
        $reqStr .= 'Content-Type: ' . HttpMessage::CONTENT_TYPE_URLENCODED . "\r\n";
        $reqStr .= 'Content-Length: ' . strlen($packedBody) . "\r\n";
        $reqStr .= 'Cookie: id=%2F; name=n' . "\r\n";
        $reqStr .= "\r\n";
        $reqStr .= $packedBody;
        if ($packedData !== $reqStr) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }
}
