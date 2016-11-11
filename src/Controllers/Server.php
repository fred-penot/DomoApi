<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

$server = $app['controllers_factory'];

$server->get('/api/services/{token}', /**
 * Retourne toutes les informations
 * sur les services proposés par l'application
 *
 * @param string $token            
 * @throws \Exception
 */
function ($token) use ($app) {
    try {
        $services = array();
        $infoHdd = $app['service.system']->getInfoHdd($app['parameter.path_hdd']);
        if ($infoHdd instanceof \Exception) {
            throw new \Exception($infoHdd->getMessage());
        }
        $infoUpnp = $app['service.system']->getInfoService($app['parameter.start_ushare']);
        if ($infoUpnp instanceof \Exception) {
            throw new \Exception($infoUpnp->getMessage());
        }
        $services[] = array(
            "name" => 'Upnp',
            "info" => $infoUpnp
        );
        $infoKodi = $app['service.system']->getInfoService($app['parameter.command_running_kodi']);
        if ($infoKodi instanceof \Exception) {
            throw new \Exception($infoKodi->getMessage());
        }
        $services[] = array(
            "name" => 'Kodi',
            "info" => $infoKodi
        );
        $app['retour'] = array(
            "infoHdd" => $infoHdd,
            "services" => $services
        );
    } catch (\Exception $ex) {
        $app['retour'] = $ex;
    }
    return new Response();
})
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$server->get('/api/service/hdd/{token}/{action}', function ($token, $action) use ($app) {
    try {
        // todo
        $execAction = false;
        if ($action == "true") {
            $execAction = true;
        }
        $serviceAction = $app['service.system']->execBooleanAction($execAction, $app['parameter.command_mount_hdd'], $app['parameter.command_umount_hdd']);
        if ($serviceAction instanceof \Exception) {
            throw new \Exception($serviceAction->getMessage());
        }
        $app['retour'] = $serviceAction;
    } catch (\Exception $ex) {
        $app['retour'] = $ex;
    }
    return new Response();
})
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$server->get('/api/service/upnp/{token}/{action}', function ($token, $action) use ($app) {
    try {
        // todo
        $execAction = false;
        if ($action == "on") {
            $execAction = true;
        }
        $serviceAction = $app['service.system']->execBooleanAction($execAction, $app['parameter.command_start_ushare'], $app['parameter.command_kill_ushare'], 1);
        if ($serviceAction instanceof \Exception) {
            throw new \Exception($serviceAction->getMessage());
        }
        $isUshareRunning = $app['service.system']->isServiceRunning($app['parameter.start_ushare']);
        if ($isUshareRunning instanceof \Exception) {
            throw new \Exception($isUshareRunning->getMessage());
        }
        $app['retour'] = array(
            "isUshareRunning" => $isUshareRunning
        );
    } catch (\Exception $ex) {
        $app['retour'] = $ex;
    }
    return new Response();
})
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$server->get('/api/service/kodi/{token}/{action}', function ($token, $action) use ($app) {
    try {
        if ($action == "off") {
            // exec('sudo /home/apps/sabNzbdScript/killKodi.sh');
        } else {
            $connection = ssh2_connect($app['parameter.ssh_host'], 22);
            ssh2_auth_password($connection, $app['parameter.ssh_login'], $app['parameter.ssh_password']);
            ssh2_exec($connection, $app['parameter.command_start_kodi']);
            $data = array(
                "kodi" => true
            );
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

$server->get('/api/service/check/kodi/{token}', function ($token) use ($app) {
    try {
        $infoService = $app['service.system']->getInfoService($app['parameter.command_running_kodi']);
        if ($infoService instanceof \Exception) {
            throw new \Exception($infoService->getMessage());
        }
        $app['retour'] = $infoService;
    } catch (\Exception $ex) {
        $app['retour'] = $ex;
    }
    return new Response();
})
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$server->get('/api/data/usage/{token}', function ($token) use ($app) {
    try {
        $ram = $app['service.system']->getDataUsageRam();
        if ($ram instanceof \Exception) {
            throw new \Exception($ram->getMessage());
        }
        $disk = $app['service.system']->getDataUsageDisk($app['parameter.mount_point_hdd']);
        if ($disk instanceof \Exception) {
            throw new \Exception($disk->getMessage());
        }
        $app['retour'] = array(
            "ram" => $ram,
            "disk" => $disk
        );
    } catch (\Exception $ex) {
        $app['retour'] = $ex;
    }
    return new Response();
})
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$server->get('/api/data/memory/free/{token}', /**
 * Libère la mémoire inutilisée par le serveur
 * et renvoie la mémoire libérée en Mo
 *
 * @param string $token            
 * @throws \Exception
 */
function ($token) use ($app) {
    try {
        $memoryFree = $app['service.system']->freeMemory($app['parameter.command_free_memory']);
        if ($memoryFree instanceof \Exception) {
            throw new \Exception($memoryFree->getMessage());
        }
        $app['retour'] = array(
            "memoryFree" => $memoryFree
        );
    } catch (\Exception $ex) {
        $app['retour'] = $ex;
    }
    return new Response();
})
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$server->get('/api/reboot/{token}', function ($token) use ($app) {
    try {
        $isDiskMounted = $app['service.system']->isDiskMounted($app['parameter.path_hdd']);
        if ($isDiskMounted instanceof \Exception) {
            throw new \Exception($isDiskMounted->getMessage());
        }
        if ($isDiskMounted) {
            $umountDisk = $app['service.system']->execBooleanAction(true, $app['parameter.command_umount_hdd']);
            if ($umountDisk instanceof \Exception) {
                throw new \Exception($umountDisk->getMessage());
            }
        }
        $app['service.system']->execBooleanAction(true, $app['parameter.command_reboot']);
    } catch (\Exception $ex) {
        $app['retour'] = $ex;
    }
    return new Response();
})
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$server->get('/api/restart/apache/{token}', function ($token) use ($app) {
    try {
        $app['service.system']->execBooleanAction(true, $app['parameter.command_apache_restart']);
    } catch (\Exception $ex) {
        $app['retour'] = $ex;
    }
    return new Response();
})
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

return $server;