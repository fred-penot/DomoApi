<?php
namespace DomoApi\Services;

class Utils
{

    public function checkDirectory($pathDir)
    {
        try {
            if (! is_dir($pathDir)) {
                if (! mkdir($pathDir, 0777, true)) {
                    throw new \Exception('Echec lors de la création du répertoire ' . $pathDir);
                }
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function deleteDirectory($pathDir)
    {
        try {
            if (is_dir($pathDir)) {
                $dir = opendir($pathDir);
                while (false !== ($file = readdir($dir))) {
                    if (($file != '.') && ($file != '..')) {
                        $element = $pathDir . '/' . $file;
                        if (is_dir($element)) {
                            $this->deleteDirectory($element);
                        } else {
                            unlink($element);
                        }
                    }
                }
                closedir($dir);
                rmdir($pathDir);
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function unzip($pathZipFile)
    {
        try {
            if (! file_exists($pathZipFile) && ! is_file($pathZipFile)) {
                throw new \Exception("Unzip : Le fichier " . $pathZipFile . " n'existe pas.");
            }
            $extension = substr(strrchr(basename($pathZipFile), '.'), 1);
            if ($extension != 'zip') {
                throw new \Exception("Unzip : Le fichier " . $pathZipFile . " n'est pas un fichier zip.");
            }
            $zip = new \ZipArchive();
            if ($zip->open($pathZipFile) === FALSE) {
                throw new \Exception("Unzip : Impossible d'ouvrir le fichier " . $pathZipFile . ".");
            }
            $zip->extractTo(dirname($pathZipFile));
            $zip->close();
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getPhpMemoryUsage()
    {
        try {
            $size = memory_get_usage(true);
            return $this->convertSize($size);
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function navigationSystem($directory = "/")
    {
        try {
            $explorer = array();
            $parent = str_replace(substr(strrchr($directory, "/"), 1), '', $directory);
            $parent = substr(str_replace("/", "|", $parent), 0, - 1);
            if ($parent == "") {
                $parent = "|";
            }
            $explorer['parent'] = $parent;
            $explorer['directories'] = array();
            $explorer['files'] = array();
            $currentDirectory = scandir($directory);
            foreach ($currentDirectory as $element) {
                if ($element != '.' && $element != '..') {
                    $infoElement = new \SplFileInfo($directory . DIRECTORY_SEPARATOR . $element);
                    $groupInfo = posix_getgrgid($infoElement->getGroup());
                    $ownerInfo = posix_getpwuid($infoElement->getOwner());
                    $elementInfo = array(
                        "path" => str_replace("/", "|", $infoElement->getPath() . DIRECTORY_SEPARATOR . $infoElement->getFilename()),
                        "size" => $this->convertSize($infoElement->getSize()),
                        "owner" => $ownerInfo['name'],
                        "group" => $groupInfo['name'],
                        "rights" => substr(sprintf('%o', $infoElement->getPerms()), - 4),
                        "readable" => $infoElement->isReadable(),
                        "writable" => $infoElement->isWritable()
                    );
                    if ($infoElement->isDir()) {
                        $dir = array();
                        $dir['name'] = $infoElement->getFilename();
                        $dir['info'] = $elementInfo;
                        $explorer['directories'][] = $dir;
                    } else {
                        $file = array();
                        $file['name'] = $infoElement->getFilename();
                        $elementInfo["extension"] = $infoElement->getExtension();
                        $elementInfo["type"] = $this->getType($infoElement->getExtension());
                        $file['info'] = $elementInfo;
                        $explorer['files'][] = $file;
                    }
                }
            }
            return $explorer;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function convertSize($size)
    {
        try {
            $unit = array(
                'o',
                'ko',
                'Mo',
                'Go',
                'To',
                'Po'
            );
            return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function getType($extension)
    {
        try {
            $video = array(
                'mp4',
                'mkv',
                'avi',
                'mpg',
                'mpeg'
            );
            $audio = array(
                'mp3',
                'wav',
                'flac',
                'ogg'
            );
            $img = array(
                'jpg',
                'jpeg',
                'gif',
                'png',
                'bmp'
            );
            $word = array(
                'doc',
                'docx'
            );
            $excel = array(
                'xls',
                'xlsx'
            );
            $powerPoint = array(
                'ppt',
                'pptx'
            );
            $code = array(
                'php',
                'js',
                'java',
                'sh',
                'html',
                'twig',
                'html.twig'
            );
            $compression = array(
                'zip',
                'rar',
                'r00',
                'r000',
                'r01',
                'r001',
                'tar',
                'gz',
                'tar.gz',
                'bz',
                '7z'
            );
            $type = 'none';
            if (in_array(strtolower($extension), $video)) {
                $type = 'video';
            } elseif (in_array(strtolower($extension), $audio)) {
                $type = 'audio';
            } elseif (in_array(strtolower($extension), $img)) {
                $type = 'image';
            } elseif (in_array(strtolower($extension), $word)) {
                $type = 'word';
            } elseif (in_array(strtolower($extension), $excel)) {
                $type = 'excel';
            } elseif (in_array(strtolower($extension), $powerPoint)) {
                $type = 'powerPoint';
            } elseif (in_array(strtolower($extension), $code)) {
                $type = 'code';
            } elseif (in_array(strtolower($extension), $compression)) {
                $type = 'compression';
            } elseif (strtolower($extension) == 'pdf') {
                $type = 'pdf';
            } elseif (strtolower($extension) == 'txt') {
                $type = 'texte';
            }
            return $type;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}