<?php
namespace Test\Http;

use Framework\Http\HttpRequest;

class TestHttpMessage extends \PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        // nothing
    }

    public function testSetAndGetHeader()
    {
        $req = new HttpRequest();
        $req->setHeader('a', 'b');
        $req->setHeader('c', 'e');
        
        if ('b' !== $req->getHeader('a')) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        if ('e' !== $req->getHeader('c')) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }

    public function testSetAndGetCookie()
    {
        $req = new HttpRequest();
        $req->setCookie('a', 'b', 30, '/a', '.a.com', true, true);
        $req->setCookie('b', '/', 30, '/a/b', '.a.com', true, true);
        
        $cookie = array(
            'value' => 'b',
            'expires' => 30,
            'path' => '/a',
            'domain' => '.a.com',
            'secure' => true,
            'httponly' => true
        );
        if ($req->getCookie('a') !== $cookie) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        
        $cookie = array(
            'value' => '/',
            'expires' => 30,
            'path' => '/a/b',
            'domain' => '.a.com',
            'secure' => true,
            'httponly' => true
        );
        if ($req->getCookie('b') !== $cookie) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        
        $allCookie = array(
            'a' => array(
                'value' => 'b',
                'expires' => 30,
                'path' => '/a',
                'domain' => '.a.com',
                'secure' => true,
                'httponly' => true
            ),
            'b' => array(
                'value' => '/',
                'expires' => 30,
                'path' => '/a/b',
                'domain' => '.a.com',
                'secure' => true,
                'httponly' => true
            )
        );
        if ($allCookie !== $req->getAllCookie()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }

    public function testSetAndGetContentType()
    {
        $req = new HttpRequest();
        $req->setContentType('abc');
        $req->setContentType('def');
        if ('def' !== $req->getContentType()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        
        if ('def' !== $req->getHeader(HttpRequest::HEADER_CONTENT_TYPE)) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }

    public function testGetSetAndAddBody()
    {
        $req = new HttpRequest();
        $req->addBody('abc');
        $req->addBody('d');
        if ('abcd' !== $req->getBody()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
        $req->setBody('abc');
        if ('abc' !== $req->getBody()) {
            throw new \PHPUnit_Framework_AssertionFailedError();
        }
    }
}
