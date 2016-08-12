<?php
namespace Test\Http;

class AsyncHttpClient extends \PHPUnit_Framework_TestCase
{

    public function request()
    {
        $sock = socket_create(AF_INET, SOCK_STREAM, TCP);
        stream_set_blocking($sock, false);
    }
}
