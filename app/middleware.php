<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Application;

// function before à positionner obligatoirement avant les routes
$checkAuth = function (Request $request, Application $app) {
    try {
        if (! $request->get('token')) {
            throw new \Exception("Veuillez fournir un token.");
        }
        $newToken = $app['service.security']->checkAndUpdateToken($request->get('token'));
        if ($newToken instanceof \Exception) {
            throw new \Exception($newToken->getMessage());
        }
        $userId = $app['service.security']->getUser($newToken);
        if ($userId instanceof \Exception) {
            throw new \Exception($userId->getMessage());
        }
        $app['newToken'] = $newToken;
        $app['user_id'] = $userId;
        return;
    } catch (\Exception $ex) {
        $app['retour'] = $ex;
        return new Response();
    }
};
// function after à positionner obligatoirement avant les routes
$jsonReturn = function (Request $request, Response $response, Application $app) {
    try {
        if ($app['retour'] instanceof \Exception) {
            throw new \Exception($app['retour']->getMessage());
        }
        if (is_array($app['retour'])) {
            if (isset($app['retour']['statut'])) {
                $statut = $app['retour']['statut'];
                $data = $app['retour']['data'];
            } else {
                $statut = true;
                $data = $app['retour'];
            }
            $data['token'] = $app['newToken'];
        } else {
            $statut = $app['retour'];
            $data = $app['newToken'];
        }
        $retour = array(
            "statut" => $statut,
            "data" => $data
        );
    } catch (\Exception $ex) {
        $app['monolog']->addError($ex->getMessage());
        $retour = array(
            "statut" => false,
            "data" => $ex->getMessage()
        );
    }
    $header = array(
        "Access-Control-Allow-Origin" => "*"
    );
    return $app->json($retour, 200, $header);
};

$jsonForexReturn = function (Request $request, Response $response, Application $app) {
    try {
        if ($app['retour'] instanceof \Exception) {
            throw new \Exception($app['retour']->getMessage());
        }
        if (is_array($app['retour'])) {
            if (isset($app['retour']['statut'])) {
                $statut = $app['retour']['statut'];
                $data = $app['retour']['data'];
            } else {
                $statut = true;
                $data = $app['retour'];
            }
        } else {
            $statut = $app['retour'];
            $data = array();
        }
        $retour = array(
            "statut" => $statut,
            "data" => $data
        );
    } catch (\Exception $ex) {
        $app['monolog']->addError($ex->getMessage());
        $retour = array(
            "statut" => false,
            "data" => $ex->getMessage()
        );
    }
    $header = array(
        "Access-Control-Allow-Origin" => "*"
    );
    return $app->json($retour, 200, $header);
};