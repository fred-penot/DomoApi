<?php
$app->mount('/light', include __DIR__ . '/../src/Controllers/Light.php');
$app->mount('/server', include __DIR__ . '/../src/Controllers/Server.php');
$app->mount('/download', include __DIR__ . '/../src/Controllers/Download.php');
$app->mount('/history', include __DIR__ . '/../src/Controllers/History.php');
$app->mount('/security', include __DIR__ . '/../src/Controllers/Security.php');
$app->mount('/usenet', include __DIR__ . '/../src/Controllers/Usenet.php');
$app->mount('/utils', include __DIR__ . '/../src/Controllers/Utils.php');
$app->mount('/forex', include __DIR__ . '/../src/Controllers/Forex.php');
$app->mount('/conversion', include __DIR__ . '/../src/Controllers/Conversion.php');
$app->mount('/allocine', include __DIR__ . '/../src/Controllers/Allocine.php');
$app->mount('/gally', include __DIR__ . '/../src/Controllers/Gally.php');
$app->mount('/freebox', include __DIR__ . '/../src/Controllers/Freebox.php');
$app->mount('/meteo', include __DIR__ . '/../src/Controllers/Meteo.php');
$app->mount('/remote', include __DIR__ . '/../src/Controllers/Remote.php');