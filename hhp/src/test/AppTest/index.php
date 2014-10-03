<?php
require_once (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Hhp' . DIRECTORY_SEPARATOR . 'App.php');

$app = Hhp\App::Instance();
$app->run(include __DIR__ . DIRECTORY_SEPARATOR . 'config.php');
?>

