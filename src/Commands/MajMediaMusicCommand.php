<?php
namespace DomoApi\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MajMediaMusicCommand extends \Knp\Command\Command {
    const MUSIC_EXTENSION = array(
        'MP3',
        'FLAC'
    );

    protected function configure() {
        $this->setName("maj:media:music")->setDescription(
            "Mets a jour les infos des médias musique du serveur");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            ini_set('memory_limit', '512M');
            $app = $this->getSilexApplication();
            $msg = "Lancement de la mise à jour des infos média du serveur";
            $app['monolog.maj_media.music']->addInfo($msg);
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
            $app['monolog.maj_media.music']->addInfo("Mise à jour terminée.");
            $output->writeln($msg);
        } catch (\Exception $ex) {
            $app['monolog.maj_media.music']->addError($ex->getMessage());
            $output->writeln("Une erreur s'est produite : " . $ex->getMessage());
        }
    }

    private function launch($output) {
        try {
            $directory = '/home/Freebox/Musiques';
            $scanDirectory = $this->scanDirectory($directory);
            if ($scanDirectory instanceof \Exception) {
                throw new \Exception($scanDirectory->getMessage());
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function scanDirectory($directory) {
        try {
            $currentDirectory = scandir($directory);
            foreach ($currentDirectory as $element) {
                if ($element != '.' && $element != '..') {
                    if (is_dir($directory . DIRECTORY_SEPARATOR . $element)) {
                        $scanDirectory = $this->scanDirectory(
                            $directory . DIRECTORY_SEPARATOR . $element);
                        if ($scanDirectory instanceof \Exception) {
                            throw new \Exception($scanDirectory->getMessage());
                        }
                    } else {
                        $extension = substr(strrchr($element, '.'), 1);
                        if (in_array(strtoupper($extension), self::MUSIC_EXTENSION)) {
                            $saveMedia = $this->saveMedia(
                                $directory . DIRECTORY_SEPARATOR . $element);
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

    private function saveMedia($url) {
        try {
            $app = $this->getSilexApplication();
            $app['monolog.maj_media.music']->addInfo($url);
            $saveMedia = false;
            $infoMedia = $app['service.analyze.media']->getMediaInfo($url);
            if ($infoMedia instanceof \Exception) {
                throw new \Exception($infoMedia->getMessage());
            }
            $app['monolog.maj_media.music']->addInfo("-" . $infoMedia['genre']);
            if ($infoMedia['genre']) {
                $genreId = $app['service.freebox.media']->createOrGetGenre($infoMedia['genre']);
                if ($genreId instanceof \Exception) {
                    throw new \Exception($genreId->getMessage());
                }
                $genreKw = $app['service.freebox.media']->createGenreKw($genreId, 
                    $infoMedia['genre']);
                if ($genreKw instanceof \Exception) {
                    throw new \Exception($genreKw->getMessage());
                }
            } else {
                $genreId = 0;
            }
            $app['monolog.maj_media.music']->addInfo("--" . $genreId);
            $artistName = $infoMedia['artist'];
            if ($artistName == "") {
                $artistName = "Artiste inconnu";
            }
            $artist = $app['service.freebox.media']->createOrGetArtist($artistName, $genreId);
            if ($artist instanceof \Exception) {
                throw new \Exception($artist->getMessage());
            }
            $app['monolog.maj_media.music']->addInfo("---" . $artist['id']);
            $artistKw = $app['service.freebox.media']->createArtistKw($artist['id'], 
                $infoMedia['artist']);
            if ($artistKw instanceof \Exception) {
                throw new \Exception($artistKw->getMessage());
            }
            $urlElement = basename($url);
            $nameDirectory = str_replace("/" . $urlElement, '', $url);
            $albumDirectoryName = substr(strrchr($nameDirectory, '/'), 1);
            $albumName = $infoMedia['album'];
            if ($albumName == "") {
                $albumName = "Album inconnu";
            }
            $checkAlbum = $app['service.freebox.media']->checkAlbum($albumDirectoryName, $albumName, 
                $artist['id'], $genreId);
            if ($checkAlbum instanceof \Exception) {
                throw new \Exception($checkAlbum->getMessage());
            }
            $app['monolog.maj_media.music']->addInfo("----" . $urlElement);
            $musicTitle = $infoMedia['title'];
            if ($musicTitle == "") {
                $musicTitle = "Titre inconnu";
            }
            if ($checkAlbum === false) {
                $app['monolog.maj_media.music']->addInfo("-----album false");
                $serviceFreebox = $app['service.freebox']->setToken($app['parameter.freebox.token']);
                $urlParent = str_replace($urlElement, "", $url);
                $sharingLink = $serviceFreebox->setSharingLink(
                    str_replace('/home/Freebox/', '/Disque dur/', $urlParent));
                if ($sharingLink instanceof \Exception) {
                    throw new \Exception($sharingLink->getMessage());
                }
                $newParentUrl = str_replace('https://fwed.freeboxos.fr:16129/', 
                    'http://mafreebox.free.fr/', $sharingLink->fullurl);
                $album = $app['service.freebox.media']->createAlbum($albumDirectoryName, $albumName, 
                    $artist['id'], $genreId, $newParentUrl);
                if ($album instanceof \Exception) {
                    throw new \Exception($album->getMessage());
                }
                $albumKw = $app['service.freebox.media']->createAlbumKw($album['id'], $albumName);
                if ($albumKw instanceof \Exception) {
                    throw new \Exception($albumKw->getMessage());
                }
                $saveMedia = true;
            } else {
                $app['monolog.maj_media.music']->addInfo("-----album true");
                $album = $checkAlbum;
                $checkMusic = $app['service.freebox.media']->checkMusic($musicTitle, $album['id'], 
                    $artist['id'], $genreId);
                if ($checkMusic instanceof \Exception) {
                    throw new \Exception($checkMusic->getMessage());
                }
                if ($checkMusic === false) {
                    $saveMedia = true;
                } else {
                    $app['monolog.maj_media.music']->addInfo("-----music connue");
                }
            }
            if ($saveMedia) {
                $app['monolog.maj_media.music']->addInfo("----- saveMedia" . $urlElement);
                $mediaRealname = str_replace(' ', '%20', $urlElement);
                $createMusic = $app['service.freebox.media']->createMusic($musicTitle, $album['id'], 
                    $artist['id'], $genreId, $mediaRealname, $infoMedia['duration']);
                if ($createMusic instanceof \Exception) {
                    throw new \Exception($createMusic->getMessage());
                }
                $createMusicKw = $app['service.freebox.media']->createMusicKw($createMusic['id'], 
                    $musicTitle);
                if ($createMusicKw instanceof \Exception) {
                    throw new \Exception($createMusicKw->getMessage());
                }
            } else {
                $app['monolog.maj_media.music']->addInfo("----- putain " . $urlElement);
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}