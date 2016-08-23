<?php
namespace Test\Http;

class HttpTestSuite extends \PHPUnit_Framework_TestSuite
{

    public function __construct()
    {
        $this->addTestSuite('Test\Http\TestHttpRequest');
        $this->addTestSuite('Test\Http\TestAsyncHttpClient');
    }
}