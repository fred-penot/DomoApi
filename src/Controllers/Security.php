<?php
use Symfony\Component\HttpFoundation\Response;
use Silex\Application;

$security = $app['controllers_factory'];

$security->get('/api/auth/{login}/{password}', 
    function ($login, $password) use($app) {
        try {
            $auth = $app['service.security']->auth($login, $password);
            if ($auth instanceof \Exception) {
                throw new \Exception($auth->getMessage());
            }
            $app['newToken'] = $auth;
            $app['retour'] = true;
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->after($jsonReturn);

$security->get('/api/check/auth/{token}', 
    function ($token) use($app) {
        try {
            $app['retour'] = true;
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

return $security;