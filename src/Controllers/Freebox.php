<?php
use Symfony\Component\HttpFoundation\Response;
use Silex\Application;

$freebox = $app['controllers_factory'];

$freebox->get('/api/build/url/{token}', 
    function ($token) use ($app) {
        try {
            $url = $app['service.freebox']->getBaseUrl();
            if ($url instanceof \Exception) {
                throw new \Exception($url->getMessage());
            }
            $app['retour'] = array(
                "url" => $url
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/get/authorize/{token}', 
    function ($token) use ($app) {
        try {
            $authorization = $app['service.freebox']->authorize("DomoApi2", "1.0", "Mon PC");
            if ($authorization instanceof \Exception) {
                throw new \Exception($authorization->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "trackId" => $authorization->result->track_id,
                    "token" => $authorization->result->app_token
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/track/authorize/{token}/{trackId}', 
    function ($token, $trackId) use ($app) {
        try {
            $serviceFreebox = $app['service.freebox']->setToken($app['parameter.freebox.token']);
            $tracking = $serviceFreebox->trackAuthorize($trackId);
            if ($tracking instanceof \Exception) {
                throw new \Exception($tracking->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "trackId" => $authorization->result->track_id,
                    "token" => $authorization->result->app_token
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/get/file/list/{token}', 
    function ($token) use ($app) {
        try {
            $serviceFreebox = $app['service.freebox']->setToken($app['parameter.freebox.token']);
            $fileList = $serviceFreebox->fileList();
            if ($fileList instanceof \Exception) {
                throw new \Exception($fileList->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "list" => $fileList
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/get/airmedia/receivers/{token}', 
    function ($token) use ($app) {
        try {
            $serviceFreebox = $app['service.freebox']->setToken($app['parameter.freebox.token']);
            $airmediaReceivers = $serviceFreebox->getAirmediaReceivers();
            if ($airmediaReceivers instanceof \Exception) {
                throw new \Exception($airmediaReceivers->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "list" => $airmediaReceivers
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/check/device/room/{token}/{room}', 
    function ($token, $room) use ($app) {
        try {
            $device = $app['service.freebox.media']->getDeviceByName($room);
            if ($device instanceof \Exception) {
                throw new \Exception($device->getMessage());
            }
            $app['retour'] = array(
                "data" => array(
                    "device" => $device
                )
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/play/airmedia/{token}/{type}/{elementId}/{deviceId}', 
    function ($token, $type, $elementId, $deviceId) use ($app) {
        try {
            $device = $app['service.freebox.media']->getDeviceById($deviceId);
            if ($device instanceof \Exception) {
                throw new \Exception($device->getMessage());
            }
            $urlMedia = $app['service.freebox.media']->getUrlMediaById($type, $elementId);
            if ($urlMedia instanceof \Exception) {
                throw new \Exception($urlMedia->getMessage());
            }
            $startPlayer = $app['service.freebox.media']->startPlayer($device['id']);
            if ($startPlayer instanceof \Exception) {
                throw new \Exception($startPlayer->getMessage());
            }
            $serviceFreebox = $app['service.freebox']->setToken($app['parameter.freebox.token']);
            $playMedia = $serviceFreebox->playMedia($type, $urlMedia, $device['realname']);
            if ($playMedia instanceof \Exception) {
                throw new \Exception($playMedia->getMessage());
            }
            $app['retour'] = array(
                "data" => $playMedia
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/play/airmedia/name/{token}/{type}/{elementName}/{deviceId}', 
    function ($token, $type, $elementName, $deviceId) use ($app) {
        try {
            $device = $app['service.freebox.media']->getDeviceById($deviceId);
            if ($device instanceof \Exception) {
                throw new \Exception($device->getMessage());
            }
            $urlMedia = $app['service.freebox.media']->getUrlMediaByName($type, $elementName);
            if ($urlMedia instanceof \Exception) {
                throw new \Exception($urlMedia->getMessage());
            }
            $startPlayer = $app['service.freebox.media']->startPlayer($device['id']);
            if ($startPlayer instanceof \Exception) {
                throw new \Exception($startPlayer->getMessage());
            }
            $serviceFreebox = $app['service.freebox']->setToken($app['parameter.freebox.token']);
            $playMedia = $serviceFreebox->playMedia($type, $urlMedia, $device['realname']);
            if ($playMedia instanceof \Exception) {
                throw new \Exception($playMedia->getMessage());
            }
            $app['retour'] = array(
                "data" => $playMedia
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/play/airmedia/name/room/{token}/{type}/{elementName}/{room}', 
    function ($token, $type, $elementName, $room) use ($app) {
        try {
            $device = $app['service.freebox.media']->getDeviceByName($room);
            if ($device instanceof \Exception) {
                throw new \Exception($device->getMessage());
            }
            $urlMedia = $app['service.freebox.media']->getUrlMediaByName($type, $elementName);
            if ($urlMedia instanceof \Exception) {
                throw new \Exception($urlMedia->getMessage());
            }
            $startPlayer = $app['service.freebox.media']->startPlayer($device['id']);
            if ($startPlayer instanceof \Exception) {
                throw new \Exception($startPlayer->getMessage());
            }
            $serviceFreebox = $app['service.freebox']->setToken($app['parameter.freebox.token']);
            $playMedia = $serviceFreebox->playMedia($type, $urlMedia, $device['realname']);
            if ($playMedia instanceof \Exception) {
                throw new \Exception($playMedia->getMessage());
            }
            $app['retour'] = array(
                "data" => $playMedia
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/stop/airmedia/room/{token}/{room}', 
    function ($token, $room) use ($app) {
        try {
            $device = $app['service.freebox.media']->getDeviceByName($room);
            if ($device instanceof \Exception) {
                throw new \Exception($device->getMessage());
            }
            $stopPlayer = $app['service.freebox.media']->stopPlayer($device['id']);
            if ($stopPlayer instanceof \Exception) {
                throw new \Exception($stopPlayer->getMessage());
            }
            $serviceFreebox = $app['service.freebox']->setToken($app['parameter.freebox.token']);
            $stopMedia = $serviceFreebox->stopMedia($device['realname']);
            if ($stopMedia instanceof \Exception) {
                throw new \Exception($stopMedia->getMessage());
            }
            $app['retour'] = array(
                "data" => $stopMedia
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/add/media/folder/room/{token}/{type}/{title}/{room}', 
    function ($token, $type, $title, $room) use ($app) {
        try {
            $device = $app['service.freebox.media']->getDeviceByName($room);
            if ($device instanceof \Exception) {
                throw new \Exception($device->getMessage());
            }
            $addMedia = $app['service.freebox.media']->addMediaFolderByName($type, $title, 
                $device['id']);
            if ($addMedia instanceof \Exception) {
                throw new \Exception($addMedia->getMessage());
            }
            $app['retour'] = array(
                "data" => $addMedia
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/add/media/item/room/{token}/{type}/{title}/{room}', 
    function ($token, $type, $title, $room) use ($app) {
        try {
            $device = $app['service.freebox.media']->getDeviceByName($room);
            if ($device instanceof \Exception) {
                throw new \Exception($device->getMessage());
            }
            $addMedia = $app['service.freebox.media']->addMediaItemByName($type, $title, 
                $device['id']);
            if ($addMedia instanceof \Exception) {
                throw new \Exception($addMedia->getMessage());
            }
            $app['retour'] = array(
                "data" => $addMedia
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/launch/playlist/room/{token}/{room}', 
    function ($token, $room) use ($app) {
        try {
            $device = $app['service.freebox.media']->getDeviceByName($room);
            if ($device instanceof \Exception) {
                throw new \Exception($device->getMessage());
            }
            $startPlayer = $app['service.freebox.media']->startPlayer($device['id']);
            if ($startPlayer instanceof \Exception) {
                throw new \Exception($startPlayer->getMessage());
            }
            $launchPlaylist = $app['service.freebox.media']->launchPlaylist($device['id']);
            if ($launchPlaylist instanceof \Exception) {
                throw new \Exception($launchPlaylist->getMessage());
            }
            $app['retour'] = array(
                "data" => $launchPlaylist
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/playlist/next/room/{token}/{room}', 
    function ($token, $room) use ($app) {
        try {
            $device = $app['service.freebox.media']->getDeviceByName($room);
            if ($device instanceof \Exception) {
                throw new \Exception($device->getMessage());
            }
            $currentMediaPlaylist = $app['service.freebox.media']->getCurrentMediaPlaylist(
                $device['id']);
            if ($currentMediaPlaylist instanceof \Exception) {
                throw new \Exception($currentMediaPlaylist->getMessage());
            }
            $nextMediaPlaylist = $app['service.freebox.media']->setNextMediaPlaylist($device['id'], 
                $currentMediaPlaylist['id']);
            if ($nextMediaPlaylist instanceof \Exception) {
                throw new \Exception($nextMediaPlaylist->getMessage());
            }
            $app['retour'] = array(
                "data" => true
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/playlist/last/room/{token}/{room}', 
    function ($token, $room) use ($app) {
        try {
            $device = $app['service.freebox.media']->getDeviceByName($room);
            if ($device instanceof \Exception) {
                throw new \Exception($device->getMessage());
            }
            $currentMediaPlaylist = $app['service.freebox.media']->getCurrentMediaPlaylist(
                $device['id']);
            if ($currentMediaPlaylist instanceof \Exception) {
                throw new \Exception($currentMediaPlaylist->getMessage());
            }
            $lastMediaPlaylist = $app['service.freebox.media']->setLastMediaPlaylist($device['id'], 
                $currentMediaPlaylist['id']);
            if ($lastMediaPlaylist instanceof \Exception) {
                throw new \Exception($lastMediaPlaylist->getMessage());
            }
            $app['retour'] = array(
                "data" => true
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/playlist/pause/room/{token}/{room}', 
    /**
     * function pause playlist
     *
     * @param string $token            
     * @param string $room            
     * @throws \Exception
     */
    function ($token, $room) use ($app) {
        try {
            $device = $app['service.freebox.media']->getDeviceByName($room);
            if ($device instanceof \Exception) {
                throw new \Exception($device->getMessage());
            }
            $pausePlaylist = $app['service.freebox.media']->pausePlaylist($device['id']);
            if ($pausePlaylist instanceof \Exception) {
                throw new \Exception($pausePlaylist->getMessage());
            }
            $serviceFreebox = $app['service.freebox']->setToken($app['parameter.freebox.token']);
            $pausePlayer = $app['service.freebox.media']->pausePlayer($device['id']);
            if ($pausePlayer instanceof \Exception) {
                throw new \Exception($pausePlayer->getMessage());
            }
            $stopMedia = $serviceFreebox->stopMedia($device['realname']);
            if ($stopMedia instanceof \Exception) {
                throw new \Exception($stopMedia->getMessage());
            }
            $app['retour'] = array(
                "data" => $stopMedia
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/playlist/resume/room/{token}/{room}', 
    /**
     * function resume playlist
     *
     * @param string $token            
     * @param string $room            
     * @throws \Exception
     */
    function ($token, $room) use ($app) {
        try {
            $device = $app['service.freebox.media']->getDeviceByName($room);
            if ($device instanceof \Exception) {
                throw new \Exception($device->getMessage());
            }
            $resumePlaylist = $app['service.freebox.media']->resumePlaylist($device['id']);
            if ($resumePlaylist instanceof \Exception) {
                throw new \Exception($resumePlaylist->getMessage());
            }
            $startPlayer = $app['service.freebox.media']->startPlayer($device['id']);
            if ($startPlayer instanceof \Exception) {
                throw new \Exception($startPlayer->getMessage());
            }
            $serviceFreebox = $app['service.freebox']->setToken($app['parameter.freebox.token']);
            $playMedia = $serviceFreebox->playMedia($resumePlaylist['type'], $resumePlaylist['url'], 
                $device['realname'], $resumePlaylist['position']);
            if ($playMedia instanceof \Exception) {
                throw new \Exception($playMedia->getMessage());
            }
            $app['retour'] = array(
                "data" => $playMedia
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/empty/playlist/room/{token}/{room}', 
    function ($token, $room) use ($app) {
        try {
            $device = $app['service.freebox.media']->getDeviceByName($room);
            if ($device instanceof \Exception) {
                throw new \Exception($device->getMessage());
            }
            $stopPlayer = $app['service.freebox.media']->stopPlayer($device['id']);
            if ($stopPlayer instanceof \Exception) {
                throw new \Exception($stopPlayer->getMessage());
            }
            $emptyPlaylist = $app['service.freebox.media']->emptyPlaylist($device['id']);
            if ($emptyPlaylist instanceof \Exception) {
                throw new \Exception($emptyPlaylist->getMessage());
            }
            $app['retour'] = array(
                "data" => $emptyPlaylist
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/get/all/tasks/{token}', 
    function ($token) use ($app) {
        try {
            $serviceFreebox = $app['service.freebox']->setToken($app['parameter.freebox.token']);
            $tasks = $serviceFreebox->getAllTasks();
            if ($tasks instanceof \Exception) {
                throw new \Exception($tasks->getMessage());
            }
            $app['retour'] = array(
                "data" => $tasks
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/get/info/media/{token}', 
    function ($token) use ($app) {
        try {
            // $url = '/home/Freebox/Musiques/compil_test/02 - Cannonball.mp3';
            // $url = '/home/Freebox/Videos/Films/HD/Evolution.1080p.mkv';
            // $url = '/home/Freebox/Videos/Films/Standard/01-Hot.Shots.mkv';
            $url = '/home/Freebox/Videos/Manga/Series/DragonBall\ Super/DragonBall\ Super-Episode46.mkv';
            $ouput = array();
            exec('mediainfo ' . $url, $ouput);
            foreach ($ouput as $line) {
                list ($index, $value) = explode(":", $line);
                if (strtoupper(trim($index)) == "DURATION") {
                    $duration = 0;
                    $timeFormat = explode(' ', trim($value));
                    foreach ($timeFormat as $time) {
                        if (strpos($time, 'h') !== false) {
                            $hour = (int) (str_replace('h', '', $time));
                            $duration += $hour * 60 * 60;
                        } elseif (strpos($time, 'mn') !== false) {
                            $minute = (int) (str_replace('mn', '', $time));
                            $duration += $minute * 60;
                        } elseif (strpos($time, 's') !== false) {
                            $duration += (int) (str_replace('s', '', $time));
                        }
                    }
                    echo $duration;
                    die();
                }
            }
            die();
            echo "<pre>";
            print_r($ouput);
            echo "</pre>";
            die();
            $infoMedia = $app['service.analyze.media']->getMediaInfo($url);
            if ($infoMedia instanceof \Exception) {
                throw new \Exception($infoMedia->getMessage());
            }
            $app['retour'] = array(
                "duration" => $infoMedia
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/get/all/sharing/link/{token}', 
    function ($token) use ($app) {
        try {
            $serviceFreebox = $app['service.freebox']->setToken($app['parameter.freebox.token']);
            $sharingLinks = $serviceFreebox->getAllSharingLink();
            if ($sharingLinks instanceof \Exception) {
                throw new \Exception($sharingLinks->getMessage());
            }
            $app['retour'] = array(
                "data" => $sharingLinks
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/set/sharing/link/{token}', 
    function ($token) use ($app) {
        try {
            $serviceFreebox = $app['service.freebox']->setToken($app['parameter.freebox.token']);
            $sharingLinks = $serviceFreebox->setSharingLink(
                '/Disque dur/Musiques/Finley.Quaye_Maverick.A.Strike');
            if ($sharingLinks instanceof \Exception) {
                throw new \Exception($sharingLinks->getMessage());
            }
            $app['retour'] = array(
                "data" => $sharingLinks
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/delete/sharing/link/{token}', 
    function ($token) use ($app) {
        try {
            $serviceFreebox = $app['service.freebox']->setToken($app['parameter.freebox.token']);
            $deleteSharingLink = $serviceFreebox->deleteSharingLink('t7lo1NxA8gYumuFk');
            if ($deleteSharingLink instanceof \Exception) {
                throw new \Exception($deleteSharingLink->getMessage());
            }
            $app['retour'] = array(
                "data" => $deleteSharingLink
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/delete/all/sharing/link/{token}', 
    function ($token) use ($app) {
        try {
            $serviceFreebox = $app['service.freebox']->setToken($app['parameter.freebox.token']);
            $sharingLinks = $serviceFreebox->getAllSharingLink();
            if ($sharingLinks instanceof \Exception) {
                throw new \Exception($sharingLinks->getMessage());
            }
            foreach ($sharingLinks as $sharingLink) {
                $serviceFreebox = $app['service.freebox']->setToken($app['parameter.freebox.token']);
                $deleteSharingLink = $serviceFreebox->deleteSharingLink($sharingLink->token);
                if ($deleteSharingLink instanceof \Exception) {
                    throw new \Exception($deleteSharingLink->getMessage());
                }
            }
            $app['retour'] = array(
                "data" => true
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

$freebox->get('/api/get/all/category/path/{token}', 
    function ($token) use ($app) {
        try {
            $categories = $app['service.freebox.media']->getAllVideoCategoryPath();
            if ($categories instanceof \Exception) {
                throw new \Exception($categories->getMessage());
            }
            echo "<pre>";
            print_r($categories);
            echo "</pre>";
            die();
            $app['retour'] = array(
                "data" => $categories
            );
        } catch (\Exception $ex) {
            $app['retour'] = $ex;
        }
        return new Response();
    })
    ->before($checkAuth, Application::EARLY_EVENT)
    ->after($jsonReturn);

return $freebox;