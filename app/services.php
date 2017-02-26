<?php
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/***** log *****/
$app['monolog.forex.archive'] = $app->share(
    function ($app) {
        $log = new $app['monolog.logger.class']('forexArchive');
        $handler = new StreamHandler($app['parameter.log.forex.archive'], Logger::DEBUG);
        $log->pushHandler($handler);
        return $log;
    });
$app['monolog.forex.current'] = $app->share(
    function ($app) {
        $log = new $app['monolog.logger.class']('forexCurrent');
        $handler = new StreamHandler($app['parameter.log.forex.current'], Logger::DEBUG);
        $log->pushHandler($handler);
        return $log;
    });
$app['monolog.domotic.gally'] = $app->share(
    function ($app) {
        $log = new $app['monolog.logger.class']('domoticGally');
        $handler = new StreamHandler($app['parameter.log.domotic.gally'], Logger::DEBUG);
        $log->pushHandler($handler);
        return $log;
    });
$app['monolog.playlist'] = $app->share(
    function ($app) {
        $log = new $app['monolog.logger.class']('domoticPlaylist');
        $handler = new StreamHandler($app['parameter.log.playlist'], Logger::DEBUG);
        $log->pushHandler($handler);
        return $log;
    });
$app['monolog.maj_media.music'] = $app->share(
    function ($app) {
        $log = new $app['monolog.logger.class']('majMediaMusic');
        $handler = new StreamHandler($app['parameter.log.maj.media.music'], Logger::DEBUG);
        $log->pushHandler($handler);
        return $log;
    });
$app['monolog.maj_media.video'] = $app->share(
    function ($app) {
        $log = new $app['monolog.logger.class']('majMediadeoVi');
        $handler = new StreamHandler($app['parameter.log.maj.media.video'], Logger::DEBUG);
        $log->pushHandler($handler);
        return $log;
    });
$app['monolog.manga.save'] = $app->share(
    function ($app) {
        $log = new $app['monolog.logger.class']('mangaSave');
        $handler = new StreamHandler($app['parameter.log.manga.save'], Logger::DEBUG);
        $log->pushHandler($handler);
        return $log;
    });
$app['monolog.manga.update'] = $app->share(
    function ($app) {
        $log = new $app['monolog.logger.class']('mangaUpdate');
        $handler = new StreamHandler($app['parameter.log.manga.update'], Logger::DEBUG);
        $log->pushHandler($handler);
        return $log;
    });
/***** service *****/

$app['service.utils'] = function ($app) {
    return new DomoApi\Services\Utils();
};
$app['service.security'] = function ($app) {
    return new DomoApi\Services\Security($app['db']);
};
$app['service.system'] = function () {
    return new DomoApi\Services\System();
};
$app['service.domotic'] = function () {
    return new DomoApi\Services\Domotic();
};
$app['service.gally'] = function ($app) {
    return new DomoApi\Services\Gally($app['db']);
};
$app['service.sabnzbd'] = function () {
    return new DomoApi\Services\Sabnzbd();
};
$app['service.usenet'] = function ($app) {
    return new DomoApi\Services\Usenet($app['db'], $app['dbs']['usenet']);
};
$app['service.japscan'] = function ($app) {
    return new DomoApi\Services\Japscan($app['db'], $app['monolog'], 
        $app['parameter.ebook.path.src'], $app['parameter.ebook.path.dest'], 
        $app['parameter.ebook.path.pdf'], $app['parameter.utils.japscan.wget'], 
        $app['parameter.utils.japscan.image_magik']);
};
$app['service.forex'] = function ($app) {
    return new DomoApi\Services\Forex($app['dbs']['forex']);
};
$app['service.conversion'] = function () {
    return new DomoApi\Services\Conversion();
};
$app['service.allocine'] = function () {
    return new DomoApi\Services\Allocine();
};
$app['service.freebox'] = function () {
    return new DomoApi\Services\Freebox();
};
$app['service.freebox.media'] = function ($app) {
    return new DomoApi\Services\FreeboxMedia($app['db']);
};
$app['service.analyze.media'] = function () {
    return new DomoApi\Services\AnalyzeMedia();
};
$app['service.meteo'] = function () {
    return new DomoApi\Services\Meteo();
};
$app['service.remote'] = function ($app) {
    return new DomoApi\Services\Remote($app['db']);
};
