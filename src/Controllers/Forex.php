<?php
use Symfony\Component\HttpFoundation\Response;

$forex = $app['controllers_factory'];

$forex->get('/api/get/cotations', 
    function () use($app) {
        try {
            $data = $app['service.forex']->getTables();
            if ($data instanceof \Exception) {
                throw new \Exception($data->getMessage());
            }
            $app['retour'] = $data;
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->after($jsonForexReturn);

$forex->get('/api/get/cotation/id/{table}/{id}', 
    function ($table, $id) use($app) {
        try {
            $data = $app['service.forex']->getById($table, $id);
            if ($data instanceof \Exception) {
                throw new \Exception($data->getMessage());
            }
            $app['retour'] = $data;
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->after($jsonForexReturn);

$forex->get('/api/get/cotation/timestamp/{table}/{timestamp}', 
    function ($table, $timestamp) use($app) {
        try {
            $data = $app['service.forex']->getByTimestamp($table, $timestamp);
            if ($data instanceof \Exception) {
                throw new \Exception($data->getMessage());
            }
            $app['retour'] = $data;
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->after($jsonForexReturn);

$forex->get('/api/get/cotation/periode/{table}/{timestamp1}/{timestamp2}', 
    function ($table, $timestamp1, $timestamp2) use($app) {
        try {
            $data = $app['service.forex']->getByPeriode($table, $timestamp1, $timestamp2);
            if ($data instanceof \Exception) {
                throw new \Exception($data->getMessage());
            }
            $app['retour'] = $data;
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->after($jsonForexReturn);

$forex->get('/api/get/current/cotations', 
    function () use($app) {
        try {
            $idAuth = $app['service.forex']->getAuthId($app['parameter.forex.url.auth'], 
                $app['parameter.forex.login'], $app['parameter.forex.password']);
            if ($idAuth instanceof \Exception) {
                throw new \Exception($idAuth->getMessage());
            }
            $data = $app['service.forex']->getCurrentCotations(
                $app['parameter.forex.url.cotations'], $idAuth);
            if ($data instanceof \Exception) {
                throw new \Exception($data->getMessage());
            }
            $app['retour'] = $data;
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->after($jsonForexReturn);

return $forex;