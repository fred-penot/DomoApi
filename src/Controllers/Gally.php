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
            $talkTo = $app['service.gally']->talkTo($name1, $name2);
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

$gally->get('/api/set/param/{token}/{sexe}/{gallyName}/{birthTimestamp}/{birthNameCity}/{birthCp}/{currentNameCity}/{currentCp}',
    function ($token, $sexe, $gallyName, $birthTimestamp, $birthNameCity, $birthCp, $currentNameCity, $currentCp) use ($app) {
        try {
            $setParam = $app['service.gally']->setParam($app['user_id'], $sexe, $gallyName, $birthTimestamp, $birthNameCity, $birthCp, $currentNameCity, $currentCp);
            if ($setParam instanceof \Exception) {
                throw new \Exception($setParam->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "setParam" => $setParam
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$gally->get('/api/get/param/{token}',
    function ($token) use ($app) {
        try {
            $getParam = $app['service.gally']->getParam($app['user_id']);
            if ($getParam instanceof \Exception) {
                throw new \Exception($getParam->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "param" => $getParam
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$gally->get('/api/set/history/{token}/{commandeVocaleId}/{timestamp}',
    function ($token, $commandeVocaleId, $timestamp) use ($app) {
        try {
            $setHistory = $app['service.gally']->setHistory($app['user_id'], $commandeVocaleId, $timestamp);
            if ($setHistory instanceof \Exception) {
                throw new \Exception($setHistory->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "setHistory" => $setHistory
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$gally->get('/api/get/history/command/day/{token}/{commandeVocaleId}/{timestamp}',
    function ($token, $commandeVocaleId, $timestamp) use ($app) {
        try {
            $getHistoryCommandDay = $app['service.gally']->getHistoryCommandDay($app['user_id'], $commandeVocaleId, $timestamp);
            if ($getHistoryCommandDay instanceof \Exception) {
                throw new \Exception($getHistoryCommandDay->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "history" => $getHistoryCommandDay
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
