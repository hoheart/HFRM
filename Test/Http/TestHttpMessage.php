<?php
namespace Test\Http;

class TestHttpMessage extends \PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $this->testGetId();
        $this->testSetAndGetURI();
    }
}
