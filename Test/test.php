<?php

function __autoload($class)
{
    if (DIRECTORY_SEPARATOR == '\\') {
        $path = $class . '.php';
    } else {
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    }
    
    $path = str_replace('Framework', '', $path);
    
    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . $path;
    
    include_once $path;
}

spl_autoload_register('__autoload');

$s = new PHPUnit_Framework_TestSuite();
$s->addTestFile('Http/TestAsyncHttpClient.php');
PHPUnit_TextUI_TestRunner::run($s);  

