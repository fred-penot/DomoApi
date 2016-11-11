<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

$download = $app['controllers_factory'];

$download->get('/api/get/all/{token}', 
    function ($token) use($app) {
        try {
            $urlSabApi = $app['parameter.sabnzbd.api.url'] . $app['parameter.sabnzbd.api.key'] .
                 $app['parameter.sabnzbd.api.mode.queue'];
            $downloads = $app['service.sabnzbd']->getAllDownload($urlSabApi);
            if ($downloads instanceof \Exception) {
                throw new \Exception($downloads->getMessage());
            }
            $app['retour'] = array(
                "downloads" => $downloads
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$download->get('/api/server/info/{token}', 
    function ($token) use($app) {
        try {
            $urlSabApi = $app['parameter.sabnzbd.api.url'] . $app['parameter.sabnzbd.api.key'] .
                 $app['parameter.sabnzbd.api.mode.queue'];
            $server = $app['service.sabnzbd']->getInfoServer($urlSabApi);
            if ($server instanceof \Exception) {
                throw new \Exception($server->getMessage());
            }
            $app['retour'] = array(
                "server" => $server
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$download->get('/api/pause/{token}/{isPause}/{id}', 
    function ($token, $isPause, $id) use($app) {
        try {
            $pause = $app['service.sabnzbd']->pause(
                $app['parameter.sabnzbd.api.url'] . $app['parameter.sabnzbd.api.key'], $isPause, 
                $app['parameter.sabnzbd.api.mode.pause'], $app['parameter.sabnzbd.api.mode.resume'], 
                $id, $app['parameter.sabnzbd.api.mode.pause.id'], 
                $app['parameter.sabnzbd.api.mode.resume.id']);
            if ($pause instanceof \Exception) {
                throw new \Exception($pause->getMessage());
            }
            $app['retour'] = $pause;
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$download->get('/api/server/speed/{token}/{limit}', 
    function ($token, $limit) use($app) {
        try {
            $urlSabApi = $app['parameter.sabnzbd.api.url'] . $app['parameter.sabnzbd.api.key'] .
                 $app['parameter.sabnzbd.api.mode.speed'] . $limit;
            $statut = $app['service.sabnzbd']->setSpeed($urlSabApi);
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

$download->get('/api/delete/{token}/{id}', 
    function ($token, $id) use($app) {
        try {
            $urlSabApi = $app['parameter.sabnzbd.api.url'] . $app['parameter.sabnzbd.api.key'] .
            $app['parameter.sabnzbd.api.mode.queue'];
            $downloads = $app['service.sabnzbd']->getAllDownload($urlSabApi);
            if ($downloads instanceof \Exception) {
                throw new \Exception($downloads->getMessage());
            }
            foreach ( $downloads as $download ) {
                if ( $download['id'] == $id ) {
                    $dirPathToDelete = $download['title'];
                    break;
                }
            }
            $urlSabApi = $app['parameter.sabnzbd.api.url'] . $app['parameter.sabnzbd.api.key'] .
                 $app['parameter.sabnzbd.api.mode.queue.delete'] . $id;
            
            $statut = $app['service.sabnzbd']->delete($urlSabApi);
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

$download->get('/api/get/categories/{token}', 
    function ($token) use($app) {
        try {
            $urlSabApi = $app['parameter.sabnzbd.api.url'] . $app['parameter.sabnzbd.api.key'] .
                 $app['parameter.sabnzbd.api.mode.categories'];
            $categories = $app['service.sabnzbd']->getCategories($urlSabApi);
            if ($categories instanceof \Exception) {
                throw new \Exception($categories->getMessage());
            }
            $app['retour'] = array(
                "categories" => $categories
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$download->get('/api/change/name/{token}/{id}/{name}', 
    function ($token, $id, $name) use($app) {
        try {
            $urlSabApi = $app['parameter.sabnzbd.api.url'] . $app['parameter.sabnzbd.api.key'] .
                 $app['parameter.sabnzbd.api.mode.change.name'];
            $statut = $app['service.sabnzbd']->change($urlSabApi, $id, $name);
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

$download->get('/api/change/category/{token}/{id}/{name}', 
    function ($token, $id, $name) use($app) {
        try {
            $urlSabApi = $app['parameter.sabnzbd.api.url'] . $app['parameter.sabnzbd.api.key'] .
                 $app['parameter.sabnzbd.api.mode.change.category'];
            $statut = $app['service.sabnzbd']->change($urlSabApi, $id, $name);
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

return $download;