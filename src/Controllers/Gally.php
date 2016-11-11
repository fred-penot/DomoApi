<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

$gally = $app['controllers_factory'];

$gally->get('/api/get/vocal/command/{token}', 
    function ($token) use ($app) {
        try {
            $commands = $app['service.gally']->getVocalCommand();
            if ($commands instanceof \Exception) {
                throw new \Exception($commands->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "command" => $commands
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$gally->get('/api/talk/to/someone/{token}/{name}/{function}', 
    function ($token, $name, $function) use ($app) {
        try {
            $talkTo = $app['service.gally']->talkTo($name, $function);
            if ($talkTo instanceof \Exception) {
                throw new \Exception($talkTo->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "talk" => $talkTo
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$gally->get('/api/talk/to/somebody/{token}/{name1}/{name2}', 
    function ($token, $name1, $name2) use ($app) {
        try {
            $commands = $app['service.gally']->talkTo($name1, $name2);
            if ($talkTo instanceof \Exception) {
                throw new \Exception($talkTo->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "talk" => $talkTo
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

return $gally;