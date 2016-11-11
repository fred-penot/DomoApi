<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

$history = $app['controllers_factory'];

$history->get('/api/get/all/{token}', function ($token) use ($app) {
    try {
        $urlSabApi = $app['parameter.sabnzbd.api.url'] . $app['parameter.sabnzbd.api.key'] . $app['parameter.sabnzbd.api.mode.history'];
        $histories = $app['service.sabnzbd']->getAllHistory($urlSabApi);
        if ($histories instanceof \Exception) {
            throw new \Exception($histories->getMessage());
        }
        $app['retour'] = array(
            "histories" => $histories
        );
    } catch (\Exception $ex) {
        $app['retour'] = $ex;
    }
    return new Response();
})
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$history->get('/api/delete/{token}/{id}', function ($token, $id) use ($app) {
    try {
        $urlSabApi = $app['parameter.sabnzbd.api.url'] . $app['parameter.sabnzbd.api.key'] . $app['parameter.sabnzbd.api.mode.history'];
        $histories = $app['service.sabnzbd']->getAllHistory($urlSabApi);
        if ($histories instanceof \Exception) {
            throw new \Exception($histories->getMessage());
        }
        foreach ($histories as $history) {
            if ($history['id'] == $id) {
                ;
                $app['service.utils']->deleteDirectory($history['pathTemp']);
                $app['service.utils']->deleteDirectory(dirname($history['pathEnd']));
                break;
            }
        }
        $urlSabApi = $app['parameter.sabnzbd.api.url'] . $app['parameter.sabnzbd.api.key'] . $app['parameter.sabnzbd.api.mode.history.delete'] . $id;
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

$history->get('/api/get/log/{token}/{id}', function ($token, $id) use ($app) {
    try {
        $urlSabApi = $app['parameter.sabnzbd.api.url'] . $app['parameter.sabnzbd.api.key'] . $app['parameter.sabnzbd.api.mode.history'];
        $log = $app['service.sabnzbd']->getLog($urlSabApi, $id);
        if ($log instanceof \Exception) {
            throw new \Exception($log->getMessage());
        }
        $app['retour'] = $log;
    } catch (\Exception $ex) {
        $app['retour'] = $ex;
    }
    return new Response();
})
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

return $history;