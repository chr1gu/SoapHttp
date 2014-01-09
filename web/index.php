<?php

require_once __DIR__.'/../vendor/autoload.php';

Symfony\Component\Debug\Debug::enable();

$app = new Silex\Application();
require __DIR__.'/../src/app.php';

$app['debug'] = true;
$app->run();
