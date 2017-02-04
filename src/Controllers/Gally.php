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

$gally->get('/api/get/ia/messages/{token}',
    function ($token) use ($app) {
        try {
            $getIaMessages = $app['service.gally']->getIaMessages();
            if ($getIaMessages instanceof \Exception) {
                throw new \Exception($getIaMessages->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "messages" => $getIaMessages
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$gally->get('/api/get/ia/search/message/{token}/{word}',
    function ($token, $word) use ($app) {
        try {
            $getIaMessagesByName = $app['service.gally']->getIaMessagesByName($word);
            if ($getIaMessagesByName instanceof \Exception) {
                throw new \Exception($getIaMessagesByName->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "messages" => $getIaMessagesByName
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$gally->get('/api/get/ia/commands/{token}',
    function ($token) use ($app) {
        try {
            $getCommandesVocale = $app['service.gally']->getCommandesVocale();
            if ($getCommandesVocale instanceof \Exception) {
                throw new \Exception($getCommandesVocale->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "commands" => $getCommandesVocale
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$gally->get('/api/get/ia/search/command/{token}/{word}',
    function ($token, $word) use ($app) {
        try {
            $getCommandesVocaleByName = $app['service.gally']->getCommandesVocaleByName($word);
            if ($getCommandesVocaleByName instanceof \Exception) {
                throw new \Exception($getCommandesVocaleByName->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "commands" => $getCommandesVocaleByName
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$gally->get('/api/save/ia/command/message/{token}/{commandId}/{messageId}/{success}/{repeat}',
    function ($token, $commandId, $messageId, $success, $repeat) use ($app) {
        try {
            $saveCommandeVocaleMessage = $app['service.gally']->saveCommandeVocaleMessage($commandId, $messageId, $success, $repeat);
            if ($saveCommandeVocaleMessage instanceof \Exception) {
                throw new \Exception($saveCommandeVocaleMessage->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "save" => $saveCommandeVocaleMessage
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$gally->get('/api/get/ia/scenarii/{token}',
    function ($token) use ($app) {
        try {
            $getIaScenarii = $app['service.gally']->getIaScenarii();
            if ($getIaScenarii instanceof \Exception) {
                throw new \Exception($getIaScenarii->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "scenarii" => $getIaScenarii
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$gally->get('/api/search/ia/name/scenarii/{token}/{word}',
    function ($token, $word) use ($app) {
        try {
            $getIaScenariiByName = $app['service.gally']->getIaScenariiByName($word);
            if ($getIaScenariiByName instanceof \Exception) {
                throw new \Exception($getIaScenariiByName->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "scenarii" => $getIaScenariiByName
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$gally->get('/api/get/ia/scenario/type/name/{token}/{type}/{word}',
    function ($token, $type, $word) use ($app) {
        try {
            $getIaScenarioByTypeAndName = $app['service.gally']->getIaScenarioByTypeAndName($type, $word);
            if ($getIaScenarioByTypeAndName instanceof \Exception) {
                throw new \Exception($getIaScenarioByTypeAndName->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "scenario" => $getIaScenarioByTypeAndName
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$gally->get('/api/launch/ia/scenario/{token}/{id}/{action}',
    function ($token, $id, $action) use ($app) {
        try {
            $launch = false;
            $scenarii = $app['service.gally']->getIaScenario($id);
            if ($scenarii instanceof \Exception) {
                throw new \Exception($scenarii->getMessage());
            }
            foreach ($scenarii as $scenario) {
                if ($scenario['type'] == 'light') {
                    $statutLight = $app['service.domotic']->putAction($scenario['value'], $action);
                    if ($statutLight instanceof \Exception) {
                        throw new \Exception($statutLight->getMessage());
                    }
                    $launch = $statutLight;
                } elseif ($scenario['type'] == 'audio') {
                    //todo
                    $launch = true;
                }
            }
            $app['retour'] = array(
                "data" => array(
                    "launch" => $launch
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
