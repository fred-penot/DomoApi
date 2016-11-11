<?php
namespace DomoApi\Services;

class Conversion {

    public function getFilesToConvert($pathDir) {
        try {
            $filesToConvert = array();
            $files = scandir($pathDir);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $filesToConvert[] = $file;
                }
            }
            return $filesToConvert;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function convertAll($pathDirIn, $pathDirEnd) {
        try {
            $files = scandir($pathDirIn);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $this->launchConvert($pathDirIn . DIRECTORY_SEPARATOR . $file, $pathDirEnd);
                }
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function convertFiles($pathDirIn, $files, $pathDirEnd) {
        try {
            foreach ($files as $file) {
                $this->launchConvert($pathDirIn . DIRECTORY_SEPARATOR . $file, $pathDirEnd);
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function convertFile($file) {
        try {
            $data = $this->launchConvert($file, dirname($file));
            if ($data instanceof \Exception) {
                throw new \Exception($data->getMessage());
            }
            return $data;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function launchConvert($file, $pathDirEnd) {
        try {
            $format = substr(strrchr(basename($file), '.'), 1);
            $fileOut = $pathDirEnd . DIRECTORY_SEPARATOR .
                 str_replace($format, 'mkv', basename($file));
            $output = array();
            exec(
                '"mkvmerge" -o "' . $fileOut .
                     '"  "--forced-track" "0:no" "--compression" "0:none" "--forced-track" "1:no" "-a" "1" "-d" "0" "-S" "-T" "--no-global-tags" "--no-chapters" "(" "' .
                     $file . '" ")" "--track-order" "0:0,0:1"', $output);
            return $output;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}