<?php
// ini_set ( 'display_errors', 'On' );
// error_reporting ( E_ALL );
require_once (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'hhp' . DIRECTORY_SEPARATOR . 'App.php');

$app = hhp\App::Instance();
$app->run();
?>