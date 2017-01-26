<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

$meteo = $app['controllers_factory'];

$meteo->get('/api/prevision/{token}/{latitude}/{longitude}/{timestamp}',
    function ($token, $latitude, $longitude, $timestamp) use($app) {
        try {
            $prevision = $app['service.meteo']->getPrevision($latitude, $longitude, $timestamp);
            if ($prevision instanceof \Exception) {
                throw new \Exception($prevision->getMessage());
            }
            $app['retour'] = array(
                "prevision" => $prevision
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$meteo->get('/api/get/cities/{token}/{word}',
    function ($token, $word) use($app) {
        try {
            $cities = $app['service.meteo']->getCities($word);
            if ($cities instanceof \Exception) {
                throw new \Exception($cities->getMessage());
            }
            $app['retour'] = array(
                "cities" => $cities
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$meteo->get('/api/get/coordinates/city/{token}/{city}/{cp}',
    function ($token, $city, $cp) use($app) {
        try {
            $coordinates = $app['service.meteo']->getCoordinatesCity($city, $cp);
            if ($coordinates instanceof \Exception) {
                throw new \Exception($coordinates->getMessage());
            }
            $app['retour'] = array(
                "coordinates" => $coordinates
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

return $meteo;
