<?php
namespace DomoApi\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MajMediaVideoCommand extends \Knp\Command\Command {
    const VIDEO_PATH = '/home/Freebox/Videos';
    const VIDEO_EXTENSION = array(
        'AVI',
        'MKV'
    );

    protected function configure() {
        $this->setName("maj:media:video")->setDescription(
            "Mets a jour les infos des médias vidéo du serveur");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            ini_set('memory_limit', '512M');
            $app = $this->getSilexApplication();
            $msg = "Lancement de la mise à jour des infos média du serveur";
            $app['monolog.maj_media.video']->addInfo($msg);
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
            $exec = $this->launch($output);
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
            $app['monolog.maj_media.video']->addInfo("Mise à jour terminée.");
            $output->writeln($msg);
        } catch (\Exception $ex) {
            $app['monolog.maj_media.video']->addError($ex->getMessage());
            $output->writeln("Une erreur s'est produite : " . $ex->getMessage());
        }
    }

    private function launch($output) {
        try {
            $app = $this->getSilexApplication();
            $videosCategoryPath = $app['service.freebox.media']->getAllVideoCategoryPath();
            if ($videosCategoryPath instanceof \Exception) {
                throw new \Exception($videosCategoryPath->getMessage());
            }
            foreach ($videosCategoryPath as $videoCategoryPath) {
                $directoryToScan = self::VIDEO_PATH . DIRECTORY_SEPARATOR .
                     implode(DIRECTORY_SEPARATOR, $videoCategoryPath['path']);
                $app['monolog.maj_media.video']->addInfo("directoryToScan => " . $directoryToScan);
                $scanDirectory = $this->scanDirectory($directoryToScan, 
                    $videoCategoryPath['categoryId']);
                if ($scanDirectory instanceof \Exception) {
                    throw new \Exception($scanDirectory->getMessage());
                }
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function scanDirectory($directory, $categoryId) {
        try {
            $currentDirectory = scandir($directory);
            foreach ($currentDirectory as $element) {
                if ($element != '.' && $element != '..') {
                    if (is_dir($directory . DIRECTORY_SEPARATOR . $element)) {
                        $app = $this->getSilexApplication();
                        $videoType = $app['service.freebox.media']->getVideoTypeByPath($element);
                        if ($videoType instanceof \Exception) {
                            throw new \Exception($videoType->getMessage());
                        }
                        $videoQuality = $app['service.freebox.media']->getVideoQualityByPath($element);
                        if ($videoQuality instanceof \Exception) {
                            throw new \Exception($videoQuality->getMessage());
                        }
                        if ( ($videoType === false) && ($videoType === false) ) {
                            $scanDirectory = $this->scanDirectory(
                                $directory . DIRECTORY_SEPARATOR . $element, $categoryId);
                            if ($scanDirectory instanceof \Exception) {
                                throw new \Exception($scanDirectory->getMessage());
                            }
                        }
                    } else {
                        $extension = substr(strrchr($element, '.'), 1);
                        if (in_array(strtoupper($extension), self::VIDEO_EXTENSION)) {
                            $saveMedia = $this->saveMedia(
                                $directory . DIRECTORY_SEPARATOR . $element, $categoryId);
                            if ($saveMedia instanceof \Exception) {
                                throw new \Exception($saveMedia->getMessage());
                            }
                        }
                    }
                }
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function saveMedia($url, $categoryId) {
        try {
            $app = $this->getSilexApplication();
            $app['monolog.maj_media.video']->addInfo($url);
            $saveMedia = false;
            $videoCategoryPath = $app['service.freebox.media']->getVideoCategoryPath($categoryId);
            if ($videoCategoryPath instanceof \Exception) {
                throw new \Exception($videoCategoryPath->getMessage());
            }
            $baseUrl = self::VIDEO_PATH . DIRECTORY_SEPARATOR .
                 implode(DIRECTORY_SEPARATOR, $videoCategoryPath);
            $filename = basename($url);
            $urlDirectory = str_replace("/" . $filename, '', $url);
            $app['monolog.maj_media.video']->addInfo("baseUrl => ".$baseUrl);
            $app['monolog.maj_media.video']->addInfo("urlDirectory => ".$urlDirectory);
            if ($baseUrl == $urlDirectory) {
                // ajout album archi
                $albumName = implode(" ", $videoCategoryPath);
            } else {
                // ajout album non archi TODO
                $albumName = substr(strrchr($urlDirectory, '/'), 1);
            }
            
            $checkAlbum = $app['service.freebox.media']->checkAlbumVideo($albumName);
            if ($checkAlbum instanceof \Exception) {
                throw new \Exception($checkAlbum->getMessage());
            }
            
            if ($checkAlbum === false) {
                $serviceFreebox = $app['service.freebox']->setToken($app['parameter.freebox.token']);
                $urlParent = str_replace($filename, "", $url);
                $sharingLink = $serviceFreebox->setSharingLink(
                    str_replace('/home/Freebox/', '/Disque dur/', $urlParent));
                if ($sharingLink instanceof \Exception) {
                    throw new \Exception($sharingLink->getMessage());
                }
                $newParentUrl = str_replace('https://fwed.freeboxos.fr:16129/', 
                    'http://mafreebox.free.fr/', $sharingLink->fullurl);
                $album = $app['service.freebox.media']->createAlbumVideo($albumName, $newParentUrl);
                if ($album instanceof \Exception) {
                    throw new \Exception($album->getMessage());
                }
                $albumKw = $app['service.freebox.media']->createAlbumVideoKw($album['id'], 
                    $albumName);
                if ($albumKw instanceof \Exception) {
                    throw new \Exception($albumKw->getMessage());
                }
                $saveMedia = true;
            } else {
                $app['monolog.maj_media.video']->addInfo("-----album true");
                $album = $checkAlbum;
                $mediaRealname = str_replace(' ', '%20', $filename);
                $checkVideo = $app['service.freebox.media']->checkVideo($categoryId, $album['id'], 
                    $mediaRealname);
                if ($checkVideo instanceof \Exception) {
                    throw new \Exception($checkVideo->getMessage());
                }
                if ($checkVideo === false) {
                    $saveMedia = true;
                } else {
                    $app['monolog.maj_media.video']->addInfo("-----video connue");
                }
            }
            
            if ($saveMedia) {
                $app['monolog.maj_media.video']->addInfo("----- saveMedia" . $filename);
                $duration = $app['service.analyze.media']->getDurationMediaVideo($url);
                if ($duration instanceof \Exception) {
                    throw new \Exception($duration->getMessage());
                }
                $mediaRealname = str_replace(' ', '%20', $filename);
                $extension = substr(strrchr($filename, '.'), 1);
                $mediaName = str_replace('.' . $extension, '', $filename);
                $createVideo = $app['service.freebox.media']->createVideo($categoryId, $album['id'], 
                    $mediaName, $mediaRealname, $duration);
                if ($createVideo instanceof \Exception) {
                    throw new \Exception($createVideo->getMessage());
                }
                $createVideoKw = $app['service.freebox.media']->createVideoKw($createVideo['id'], 
                    $mediaName);
                if ($createVideoKw instanceof \Exception) {
                    throw new \Exception($createVideoKw->getMessage());
                }
            } else {
                $app['monolog.maj_media.video']->addInfo("----- putain " . $filename);
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}