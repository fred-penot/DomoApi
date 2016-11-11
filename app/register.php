<?php
// register bdd
$app->register(new Silex\Provider\DoctrineServiceProvider(), 
    
    array(
        'dbs.options' => array(
            'local' => array(
                'driver' => $app['parameter.db.driver'],
                'host' => $app['parameter.db.host'],
                'dbname' => $app['parameter.db.name'],
                'user' => $app['parameter.db.login'],
                'password' => $app['parameter.db.password'],
                'charset' => 'utf8'
            ),
            'usenet' => array(
                'driver' => $app['parameter.db.driver'],
                'host' => $app['parameter.db.usenet.host'],
                'dbname' => $app['parameter.db.usenet.name'],
                'user' => $app['parameter.db.login'],
                'password' => $app['parameter.db.password'],
                'charset' => 'utf8'
            ),
            'forex' => array(
                'driver' => $app['parameter.db.driver'],
                'host' => $app['parameter.db.forex.host'],
                'dbname' => $app['parameter.db.forex.name'],
                'user' => $app['parameter.db.forex.login'],
                'password' => $app['parameter.db.forex.password'],
                'charset' => 'utf8'
            )
        )
    ));

// register log
$app->register(new Silex\Provider\MonologServiceProvider(), 
    array(
        'monolog.logfile' => __DIR__ . '/log/' . $app['parameter.log.name']
    ));

// register console
$app->register(new \Knp\Provider\ConsoleServiceProvider(), 
    array(
        'console.name' => 'DomoApiConsole',
        'console.version' => '0.1.0',
        'console.project_directory' => __DIR__ . "/.."
    ));