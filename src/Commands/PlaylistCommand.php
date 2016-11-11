<?php
namespace DomoApi\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PlaylistCommand extends \Knp\Command\Command {

    protected function configure() {
        $this->setName("playlist:launch")
        ->setDescription("Lance la playlist")
        ->addArgument('roomId');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            ini_set('memory_limit', '512M');
            $app = $this->getSilexApplication();
            $msg = "Lancement de la playlist";
            $app['monolog.playlist']->addInfo($msg);
            $output->writeln($msg);
            $memoryFree = $app['service.system']->freeMemory($app['parameter.command_free_memory']);
            if ($memoryFree instanceof \Exception) {
                throw new \Exception($memoryFree->getMessage());
            }
            $phpMemoryUsage = $app['service.utils']->getPhpMemoryUsage();
            if ($phpMemoryUsage instanceof \Exception) {
                throw new \Exception($phpMemoryUsage->getMessage());
            }
            $output->writeln("Mémoire utilisée au lancement : " . $phpMemoryUsage);
            $roomId = $input->getArgument('roomId');
            $app['monolog.playlist']->addInfo("roomId : " . $roomId);
            $exec = $this->launch($roomId, $output);
            if ($exec instanceof \Exception) {
                throw new \Exception($exec->getMessage());
            }
            $memoryFree = $app['service.system']->freeMemory($app['parameter.command_free_memory']);
            if ($memoryFree instanceof \Exception) {
                throw new \Exception($memoryFree->getMessage());
            }
            $phpMemoryUsage = $app['service.utils']->getPhpMemoryUsage();
            if ($phpMemoryUsage instanceof \Exception) {
                throw new \Exception($phpMemoryUsage->getMessage());
            }
            $output->writeln("Mémoire utilisée à la coupure : " . $phpMemoryUsage);
            $app['monolog.playlist']->addInfo($msg);
            $output->writeln($msg);
        } catch (\Exception $ex) {
            $app['monolog.playlist']->addError($ex->getMessage());
            $output->writeln("Une erreur s'est produite : " . $ex->getMessage());
        }
    }

    private function launch($roomId, $output) {
        try {
            $app = $this->getSilexApplication();
            $device = $app['service.freebox.media']->getDeviceById($roomId);
            if ($device instanceof \Exception) {
                throw new \Exception($device->getMessage());
            }
            $playlist = $app['service.freebox.media']->getPlaylist($device['id']);
            if ($playlist instanceof \Exception) {
                throw new \Exception($playlist->getMessage());
            }
            if (count($playlist) > 0) {
                $stopPlaylist = false;
                $nextMedia = $app['service.freebox.media']->getNextMediaPlaylist($device['id']);
                if ($nextMedia instanceof \Exception) {
                    throw new \Exception($nextMedia->getMessage());
                }
                while (count($playlist) > 0 && ! $stopPlaylist) {
                    list ($timestampStart, $mediaLength) = $this->play($device, $nextMedia);
                    $currentTimestamp = time();
                    $timestampEnd = $timestampStart + $mediaLength;
                    $refreshTime = $currentTimestamp;
                    while ($timestampEnd > $currentTimestamp) {
                        $playerActivity = $app['service.freebox.media']->getPlaylistActivity(
                            $device['id']);
                        if ($playerActivity instanceof \Exception) {
                            throw new \Exception($playerActivity->getMessage());
                        }
                        if (! $playerActivity['stop']) {
                            $mediaInfo = $app['service.freebox.media']->getMediaPlaylistById(
                                $nextMedia['id']);
                            if ($mediaInfo instanceof \Exception) {
                                throw new \Exception($mediaInfo->getMessage());
                            }
                            if ($mediaInfo['current']) {
                                if (! $mediaInfo['pause']) {
                                    $currentTimestamp = time();
                                } else {
                                    $currentTimestamp = 0;
                                    $timeElapsed = $mediaInfo['timestamp_pause'] -
                                         $mediaInfo['timestamp_start'];
                                    $timestampEnd = time() + ($mediaLength - $timeElapsed);
                                }
                                if ((time() - $refreshTime) > 300) {
                                    $memoryFree = $app['service.system']->freeMemory(
                                        $app['parameter.command_free_memory']);
                                    if ($memoryFree instanceof \Exception) {
                                        throw new \Exception($memoryFree->getMessage());
                                    }
                                    gc_collect_cycles();
                                    $refreshTime = time();
                                }
                            } else {
                                $timestampEnd = 0;
                            }
                        } else {
                            $timestampEnd = 0;
                        }
                        gc_collect_cycles();
                    }
                    $app['monolog.playlist']->addInfo("Media terminé");
                    $lastMedia = $nextMedia;
                    $memoryFree = $app['service.system']->freeMemory(
                        $app['parameter.command_free_memory']);
                    if ($memoryFree instanceof \Exception) {
                        throw new \Exception($memoryFree->getMessage());
                    }
                    $playlist = $app['service.freebox.media']->getPlaylist($device['id']);
                    if ($playlist instanceof \Exception) {
                        throw new \Exception($playlist->getMessage());
                    }
                    if (count($playlist > 0)) {
                        $app['monolog.playlist']->addInfo("continue playlist");
                        $playerActivity = $app['service.freebox.media']->getPlaylistActivity(
                            $device['id']);
                        if ($playerActivity instanceof \Exception) {
                            throw new \Exception($playerActivity->getMessage());
                        }
                        if ($playerActivity['stop']) {
                            $app['monolog.playlist']->addInfo("stop playlist");
                            $stopPlaylist = true;
                            $serviceFreebox = $app['service.freebox']->setToken(
                                $app['parameter.freebox.token']);
                            $stopMedia = $serviceFreebox->stopMedia($device['realname']);
                            if ($stopMedia instanceof \Exception) {
                                throw new \Exception($stopMedia->getMessage());
                            }
                        } else {
                            $app['monolog.playlist']->addInfo("don't stop playlist");
                            $currentMedia = $app['service.freebox.media']->getCurrentMediaPlaylist(
                                $device['id']);
                            if ($currentMedia instanceof \Exception) {
                                throw new \Exception($currentMedia->getMessage());
                            }
                            if ($currentMedia['id'] == $lastMedia['id']) {
                                $app['monolog.playlist']->addInfo("changement auto");
                                $setNextMedia = $app['service.freebox.media']->setNextMediaPlaylist(
                                    $device['id'], $currentMedia['id']);
                                if ($setNextMedia instanceof \Exception) {
                                    throw new \Exception($setNextMedia->getMessage());
                                }
                                $currentMedia = $app['service.freebox.media']->getCurrentMediaPlaylist(
                                    $device['id']);
                                if ($currentMedia instanceof \Exception) {
                                    throw new \Exception($currentMedia->getMessage());
                                }
                            }
                            $nextMedia = $currentMedia;
                        }
                    }
                }
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function play($device, $media) {
        try {
            $app = $this->getSilexApplication();
            $mediaPlaylist = $app['service.freebox.media']->getMediaPlaylist($media);
            if ($mediaPlaylist instanceof \Exception) {
                throw new \Exception($mediaPlaylist->getMessage());
            }
            
            $app['monolog.playlist']->addInfo("type = " . $mediaPlaylist['type']);
            $app['monolog.playlist']->addInfo("url = " . $mediaPlaylist['url']);
            $app['monolog.playlist']->addInfo("device = " . $device['realname']);
            $app['monolog.playlist']->addInfo(
                "timestampStart = " . $mediaPlaylist['timestampStart']);
            $app['monolog.playlist']->addInfo("length = " . $mediaPlaylist['length']);
            
            $serviceFreebox = $app['service.freebox']->setToken($app['parameter.freebox.token']);
            $stopMedia = $serviceFreebox->stopMedia($device['realname']);
            if ($stopMedia instanceof \Exception) {
                throw new \Exception($stopMedia->getMessage());
            }
            // $app['monolog.playlist']->addInfo("stopMedia = " . $stopMedia);
            $serviceFreebox = $app['service.freebox']->setToken($app['parameter.freebox.token']);
            $playMedia = $serviceFreebox->playMedia($mediaPlaylist['type'], $mediaPlaylist['url'], 
                $device['realname']);
            if ($playMedia instanceof \Exception) {
                throw new \Exception($playMedia->getMessage());
            }
            // $app['monolog.playlist']->addInfo("playMedia = " . $playMedia);
            $updateMediaPlaylist = $app['service.freebox.media']->setCurrentMediaPlaylist(
                $device['id'], $media['id']);
            if ($updateMediaPlaylist instanceof \Exception) {
                throw new \Exception($updateMediaPlaylist->getMessage());
            }
            return array(
                $mediaPlaylist['timestampStart'],
                $mediaPlaylist['length']
            );
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}