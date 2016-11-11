<?php
zray_disable(true);

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application();

require __DIR__ . '/../app/bootstrap.php';

$app->run();