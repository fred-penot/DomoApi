<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

$remote = $app['controllers_factory'];

$remote->get('/api/save/action/{token}/{action}',
    function ($token, $action) use ($app) {
        try {
            $saveAction = $app['service.remote']->saveAction($app['user_id'], $action);
            if ($saveAction instanceof \Exception) {
                throw new \Exception($saveAction->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "save" => $saveAction
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$remote->get('/api/get/action/{token}',
    function ($token) use ($app) {
        try {
            $getAction = $app['service.remote']->getAction($app['user_id']);
            if ($getAction instanceof \Exception) {
                throw new \Exception($getAction->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "action" => $getAction
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

return $remote;
