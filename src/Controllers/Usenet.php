<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

$usenet = $app['controllers_factory'];

$usenet->get('/api/get/category/{token}', 
    function ($token) use ($app) {
        try {
            $data = $app['service.usenet']->getAllCategory();
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

$usenet->get('/api/get/subcategory/{token}', 
    function ($token) use ($app) {
        try {
            $data = $app['service.usenet']->getAllSubCategory();
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

$usenet->get('/api/get/subcategory/by/category/{token}/{id}', 
    function ($token, $id) use ($app) {
        try {
            $data = $app['service.usenet']->getSubCategoryByCategory($id);
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

$usenet->get('/api/get/by/id/{token}/{id}', 
    function ($token, $id) use ($app) {
        try {
            $data = $app['service.usenet']->getById($id);
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

$usenet->get('/api/get/period/by/subcategory/{token}/{subCategoryId}/{timestamp1}/{timestamp2}', 
    function ($token, $subCategoryId, $timestamp1, $timestamp2) use ($app) {
        try {
            $data = $app['service.usenet']->getBySubCategoryAndPeriod($subCategoryId, $timestamp1, 
                $timestamp2);
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

$usenet->get('/api/get/last/by/subcategory/{token}/{subCategoryId}/{limit}', 
    function ($token, $subCategoryId, $limit) use ($app) {
        try {
            $data = $app['service.usenet']->getLastBySubCategory($subCategoryId, $limit);
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

$usenet->get('/api/get/day/by/subcategory/{token}/{subCategoryId}/{timestamp}', 
    function ($token, $subCategoryId, $timestamp) use ($app) {
        try {
            $data = $app['service.usenet']->getDayBySubCategory($subCategoryId, $timestamp);
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

$usenet->get('/api/get/nzb/{token}/{id}', 
    function ($token, $id) use ($app) {
        try {
            $data = $app['service.usenet']->findNzb($id);
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

$usenet->get('/api/send/nzb/{token}/{id}/{title}/{category}', 
    function ($token, $id, $title, $category) use ($app) {
        try {
            $urlNzb = $app['service.usenet']->getUrlNzb($id);
            if ($urlNzb instanceof \Exception) {
                throw new \Exception($urlNzb->getMessage());
            }
            $urlSabApi = $app['parameter.sabnzbd.api.url'] . $app['parameter.sabnzbd.api.key'] .
                 $app['parameter.sabnzbd.api.mode.add.url'];
            if ($category != null) {
                $urlSabApi .= str_replace(":category", $category, 
                    $app['parameter.sabnzbd.api.mode.add.url.category']);
            }
            $statut = $app['service.sabnzbd']->sendUrlNzb($urlSabApi, $urlNzb, $title);
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

return $usenet;