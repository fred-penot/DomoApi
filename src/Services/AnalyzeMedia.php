<?php
namespace DomoApi\Services;

use \GetId3_GetId3 as GetId3;

class AnalyzeMedia {

    public function __construct() {}

    public function __destruct() {}

    public function getMediaInfo($url) {
        try {
            $infoMedia = $this->getInfoMedia($url);
            if ($infoMedia instanceof \Exception) {
                throw new \Exception($infoMedia->getMessage());
            }
            $title = "";
            $album = "";
            $artist = "";
            $genre = "";
            $extension = substr(strrchr($url, '.'), 1);
            if (strtoupper($extension) == 'FLAC') {
                if (isset($infoMedia['tags'])) {
                    if (isset($infoMedia['tags']['vorbiscomment'])) {
                        if (isset($infoMedia['tags']['vorbiscomment']['title'])) {
                            $title = $infoMedia['tags']['vorbiscomment']['title'][0];
                        }
                        if (isset($infoMedia['tags']['vorbiscomment']['album'])) {
                            $album = $infoMedia['tags']['vorbiscomment']['album'][0];
                        }
                        if (isset($infoMedia['tags']['vorbiscomment']['artist'])) {
                            $artist = $infoMedia['tags']['vorbiscomment']['artist'][0];
                        }
                        if (isset($infoMedia['tags']['vorbiscomment']['genre'])) {
                            $genre = $infoMedia['tags']['vorbiscomment']['genre'][0];
                        }
                    }
                }
                if (isset($infoMedia['tags_html'])) {
                    if (isset($infoMedia['tags_html']['vorbiscomment'])) {
                        if (isset($infoMedia['tags_html']['vorbiscomment']['title'])) {
                            if ($title == "") {
                                $title = $infoMedia['tags_html']['vorbiscomment']['title'][0];
                            }
                        }
                        if (isset($infoMedia['tags_html']['vorbiscomment']['album'])) {
                            if ($album == "") {
                                $album = $infoMedia['tags_html']['vorbiscomment']['album'][0];
                            }
                        }
                        if (isset($infoMedia['tags_html']['vorbiscomment']['artist'])) {
                            if ($artist == "") {
                                $artist = $infoMedia['tags_html']['vorbiscomment']['artist'][0];
                            }
                        }
                        if (isset($infoMedia['tags_html']['vorbiscomment']['genre'])) {
                            if ($genre == "") {
                                $genre = $infoMedia['tags_html']['vorbiscomment']['genre'][0];
                            }
                        }
                        $band = "";
                        if (isset($infoMedia['tags_html']['vorbiscomment']['band'])) {
                            $band = $infoMedia['tags_html']['vorbiscomment']['band'][0];
                        }
                        if ($title == "") {
                            $title = $album;
                            if ($band != "") {
                                $album = $band;
                            }
                        }
                    }
                }
            } else {
                if (isset($infoMedia['tags'])) {
                    if (isset($infoMedia['tags']['id3v1'])) {
                        if (isset($infoMedia['tags']['id3v1']['title'])) {
                            $title = $infoMedia['tags']['id3v1']['title'][0];
                        }
                        if (isset($infoMedia['tags']['id3v1']['album'])) {
                            $album = $infoMedia['tags']['id3v1']['album'][0];
                        }
                        if (isset($infoMedia['tags']['id3v1']['artist'])) {
                            $artist = $infoMedia['tags']['id3v1']['artist'][0];
                        }
                        if (isset($infoMedia['tags']['id3v1']['genre'])) {
                            $genre = $infoMedia['tags']['id3v1']['genre'][0];
                        }
                    }
                }
                if (isset($infoMedia['tags_html'])) {
                    if (isset($infoMedia['tags_html']['id3v1'])) {
                        if (isset($infoMedia['tags_html']['id3v1']['title'])) {
                            if ($title == "") {
                                $title = $infoMedia['tags_html']['id3v1']['title'][0];
                            }
                        }
                        if (isset($infoMedia['tags_html']['id3v1']['album'])) {
                            if ($album == "") {
                                $album = $infoMedia['tags_html']['id3v1']['album'][0];
                            }
                        }
                        if (isset($infoMedia['tags_html']['id3v1']['artist'])) {
                            if ($artist == "") {
                                $artist = $infoMedia['tags_html']['id3v1']['artist'][0];
                            }
                        }
                        if (isset($infoMedia['tags_html']['id3v1']['genre'])) {
                            if ($genre == "") {
                                $genre = $infoMedia['tags_html']['id3v1']['genre'][0];
                            }
                        }
                        $band = "";
                        if (isset($infoMedia['tags_html']['id3v1']['band'])) {
                            $band = $infoMedia['tags_html']['id3v1']['band'][0];
                        }
                        if ($title == "") {
                            $title = $album;
                            if ($band != "") {
                                $album = $band;
                            }
                        }
                    }
                }
            }
            
            $retour = array(
                "title" => $title,
                "format" => $infoMedia['fileformat'],
                "album" => $album,
                "artist" => $artist,
                "genre" => $genre,
                "duration" => (int) $infoMedia['playtime_seconds']
            );
            return $retour;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getDurationMedia($url) {
        try {
            $infoMedia = $this->getInfoMedia($url);
            if ($infoMedia instanceof \Exception) {
                throw new \Exception($infoMedia->getMessage());
            }
            return (int) $infoMedia['playtime_seconds'];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getDurationMediaVideo($url) {
        try {
            $urlFormat = str_replace(' ', '\ ', $url);
            $ouput = array();
            exec('mediainfo ' . $urlFormat, $ouput);
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
                    return $duration;
                }
            }
            return 0;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function getInfoMedia($url) {
        try {
            $ThisFileInfo = null;
            if ($fp_remote = fopen($url, 'rb')) {
                $localtempfilename = tempnam('/tmp', 'getID3');
                if ($fp_local = fopen($localtempfilename, 'wb')) {
                    while ($buffer = fread($fp_remote, 8192)) {
                        fwrite($fp_local, $buffer);
                    }
                    fclose($fp_local);
                    // Initialize getID3 engine
                    $getID3 = new GetId3();
                    $ThisFileInfo = $getID3->analyze($localtempfilename);
                    // Delete temporary file
                    unlink($localtempfilename);
                }
                fclose($fp_remote);
            }
            return $ThisFileInfo;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}