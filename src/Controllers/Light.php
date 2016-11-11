<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

$light = $app['controllers_factory'];

$light->get('/api/devices/{token}', 
    function ($token) use($app) {
        try {
            $listeDevice = $app['service.domotic']->getDevices();
            if ($listeDevice instanceof \Exception) {
                throw new \Exception($listeDevice->getMessage());
            }
            $app['retour'] = array(
                "listeDevice" => $listeDevice
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$light->get('/api/put/{token}/{action}/{name}', 
    function ($token, $action, $name) use($app) {
        try {
            $statut = $app['service.domotic']->putAction($name, $action);
            if ($statut instanceof \Exception) {
                throw new \Exception($statut->getMessage());
            }
            $app['retour'] = $statut;
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

return $light;