<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

$allocine = $app['controllers_factory'];

$allocine->get('/api/search/movie/{token}/{title}', 
    function ($token, $title) use ($app) {
        try {
            $data = $app['service.allocine']->searchMovie($title);
            if ($data instanceof \Exception) {
                throw new \Exception($data->getMessage());
            }
            $app['retour'] = array(
                "data" => $data
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$allocine->get('/api/get/movie/{token}/{code}', 
    function ($token, $code) use ($app) {
        try {
            $data = $app['service.allocine']->getMovie($code);
            if ($data instanceof \Exception) {
                throw new \Exception($data->getMessage());
            }
            $app['retour'] = array(
                "data" => $data
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$allocine->get('/api/search/serie/{token}/{title}', 
    function ($token, $title) use ($app) {
        try {
            $data = $app['service.allocine']->searchSerie($title);
            if ($data instanceof \Exception) {
                throw new \Exception($data->getMessage());
            }
            $app['retour'] = array(
                "data" => $data
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$allocine->get('/api/get/serie/{token}/{code}', 
    function ($token, $code) use ($app) {
        try {
            $data = $app['service.allocine']->getSerie($code);
            if ($data instanceof \Exception) {
                throw new \Exception($data->getMessage());
            }
            $app['retour'] = array(
                "data" => $data
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

return $allocine;