<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

$meteo = $app['controllers_factory'];

$meteo->get('/api/prevision/{token}', 
    function ($token) use($app) {
        try {
            $prevision = $app['service.meteo']->getPrevision();
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

return $meteo;