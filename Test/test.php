<?php
use Test\Http\HttpTestSuite;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

function autoloadFramework($class)
{
    if (DIRECTORY_SEPARATOR == '\\') {
        $path = $class . '.php';
    } else {
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    }
    
    $path = str_replace('Framework', '', $path);
    
    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . $path;
    if (file_exists($path)) {
        include_once $path;
    }
}

spl_autoload_register('autoloadFramework');

$s = new PHPUnit_Framework_TestSuite();
$s->addTestSuite(new HttpTestSuite());
PHPUnit_TextUI_TestRunner::run($s);  

