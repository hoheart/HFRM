<?php
namespace Framework\Net;

interface IConnectionManager
{
    public function onConnect($sock);
}