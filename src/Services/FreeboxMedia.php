<?php
namespace DomoApi\Services;

class FreeboxMedia {
    private $db = null;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getDeviceById($id) {
        try {
            $sql = "SELECT * FROM freebox_device_player WHERE id=? ;";
            $result = $this->db->fetchAssoc($sql, 
                array(
                    (int) $id
                ));
            return $result;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getDeviceByName($name) {
        try {
            $sqlDeviceKw = "SELECT DISTINCT freebox_device_player_id FROM freebox_device_player_kw WHERE UPPER(name)='" .
                 strtoupper(str_replace('%20', ' ', $name)) . "' ;";
            $resultDeviceKw = $this->db->fetchAll($sqlDeviceKw);
            if (count($resultDeviceKw) == 0) {
                throw new \Exception("Pas de correspondance pour le matériel " . $name . " !");
            }
            $sqlDevice = "SELECT * FROM freebox_device_player WHERE id=" .
                 $resultDeviceKw[0]['freebox_device_player_id'] . " ;";
            $resultDevice = $this->db->fetchAll($sqlDevice);
            return $resultDevice[0];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getUrlMediaById($type, $elementId) {
        try {
            if ($type == 'audio') {
                $sqlMusic = "SELECT * FROM freebox_music WHERE id=? ;";
                $resultMusic = $this->db->fetchAssoc($sqlMusic, 
                    array(
                        (int) $elementId
                    ));
                $sqlAlbum = "SELECT * FROM freebox_music_album WHERE id=? ;";
                $resultAlbum = $this->db->fetchAssoc($sqlAlbum, 
                    array(
                        (int) $resultMusic['freebox_music_album_id']
                    ));
                $urlMedia = $resultAlbum['url'] . $resultMusic['realname'];
            } elseif ($type == 'video') {
                $sqlVideo = "SELECT * FROM freebox_video WHERE id=? ;";
                $resultVideo = $this->db->fetchAssoc($sqlVideo, 
                    array(
                        (int) $elementId
                    ));
                $sqlAlbum = "SELECT * FROM freebox_video_album WHERE id=? ;";
                $resultAlbum = $this->db->fetchAssoc($sqlAlbum, 
                    array(
                        (int) $resultVideo['freebox_video_album_id']
                    ));
                $urlMedia = $resultAlbum['url'] . $resultVideo['realname'];
            } elseif ($type == 'photo') {
                $sqlPhoto = "SELECT * FROM freebox_photo WHERE id=? ;";
                $resultPhoto = $this->db->fetchAssoc($sqlPhoto, 
                    array(
                        (int) $elementId
                    ));
                $sqlAlbum = "SELECT * FROM freebox_photo_album WHERE id=? ;";
                $resultAlbum = $this->db->fetchAssoc($sqlAlbum, 
                    array(
                        (int) $resultPhoto['freebox_photo_album_id']
                    ));
                $urlMedia = $resultAlbum['url'] . $resultPhoto['realname'];
            }
            return $urlMedia;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getUrlMediaByName($type, $elementName) {
        try {
            if ($type == 'audio') {
                $sqlMusicKw = "SELECT DISTINCT freebox_music_id FROM freebox_music_kw WHERE UPPER(name)='" .
                     strtoupper(str_replace('%20', ' ', $elementName)) . "' ;";
                $resultMusicKw = $this->db->fetchAll($sqlMusicKw);
                if (count($resultMusicKw) == 0) {
                    throw new \Exception(
                        "Pas de correspondance pour le titre " . $elementName . " !");
                }
                $sqlMusic = "SELECT * FROM freebox_music WHERE id=" .
                     $resultMusicKw[0]['freebox_music_id'] . " ;";
                $resultMusic = $this->db->fetchAll($sqlMusic);
                $sqlAlbum = "SELECT * FROM freebox_music_album WHERE id=? ;";
                $resultAlbum = $this->db->fetchAssoc($sqlAlbum, 
                    array(
                        (int) $resultMusic[0]['freebox_music_album_id']
                    ));
                $urlMedia = $resultAlbum['url'] . $resultMusic[0]['realname'];
            } elseif ($type == 'video') {
                $sqlVideoKw = "SELECT DISTINCT freebox_video_id FROM freebox_video_kw WHERE UPPER(name)='" .
                     strtoupper(str_replace('%20', ' ', $elementName)) . "' ;";
                $resultVideoKw = $this->db->fetchAll($sqlVideoKw);
                if (count($resultVideoKw) == 0) {
                    throw new \Exception(
                        "Pas de correspondance pour le titre " . $elementName . " !");
                }
                $sqlVideo = "SELECT * FROM freebox_video WHERE id=" .
                     $resultVideoKw[0]['freebox_video_id'] . " ;";
                $resultVideo = $this->db->fetchAll($sqlVideo);
                $sqlAlbum = "SELECT * FROM freebox_video_album WHERE id=? ;";
                $resultAlbum = $this->db->fetchAssoc($sqlAlbum, 
                    array(
                        (int) $resultVideo[0]['freebox_video_album_id']
                    ));
                $urlMedia = $resultAlbum['url'] . $resultVideo[0]['realname'];
            } elseif ($type == 'photo') {
                $sqlPhotoKw = "SELECT DISTINCT freebox_photo_id FROM freebox_photo_kw WHERE UPPER(name)='" .
                     strtoupper(str_replace('%20', ' ', $elementName)) . "' ;";
                $resultPhotoKw = $this->db->fetchAll($sqlPhotoKw);
                if (count($resultPhotoKw) == 0) {
                    throw new \Exception(
                        "Pas de correspondance pour la photo " . $elementName . " !");
                }
                $sqlPhoto = "SELECT * FROM freebox_photo WHERE id=" .
                     $resultPhotoKw[0]['freebox_photo_id'] . " ;";
                $resultPhoto = $this->db->fetchAll($sqlPhoto);
                $sqlAlbum = "SELECT * FROM freebox_photo_album WHERE id=? ;";
                $resultAlbum = $this->db->fetchAssoc($sqlAlbum, 
                    array(
                        (int) $resultPhoto[0]['freebox_photo_album_id']
                    ));
                $urlMedia = $resultAlbum['url'] . $resultPhoto[0]['realname'];
            }
            return $urlMedia;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function addMediaFolderByName($type, $elementName, $deviceId) {
        try {
            if ($type == 'audio') {
                $sqlAlbumKw = "SELECT DISTINCT freebox_music_album_id FROM freebox_music_album_kw WHERE UPPER(name)='" .
                     strtoupper(str_replace('%20', ' ', $elementName)) . "' ;";
                $resultAlbumKw = $this->db->fetchAll($sqlAlbumKw);
                if (count($resultAlbumKw) == 0) {
                    throw new \Exception(
                        "Pas de correspondance pour le titre " . $elementName . " !");
                }
                $sqlAlbum = "SELECT * FROM freebox_music_album WHERE id=" .
                     $resultAlbumKw[0]['freebox_music_album_id'] . " ;";
                $resultAlbum = $this->db->fetchAll($sqlAlbum);
                $sqlMusic = "SELECT * FROM freebox_music WHERE freebox_music_album_id=? ;";
                $resultMusic = $this->db->fetchAll($sqlMusic, 
                    array(
                        (int) $resultAlbum[0]['id']
                    ));
                foreach ($resultMusic as $music) {
                    $this->insertMediaPlayList($type, $deviceId, $music['id']);
                }
            } elseif ($type == 'video') {
                $sqlAlbumKw = "SELECT DISTINCT freebox_video_album_id FROM freebox_video_album_kw WHERE UPPER(name)='" .
                     strtoupper(str_replace('%20', ' ', $elementName)) . "' ;";
                $resultAlbumKw = $this->db->fetchAll($sqlAlbumKw);
                if (count($resultAlbumKw) == 0) {
                    throw new \Exception(
                        "Pas de correspondance pour la vidéo " . $elementName . " !");
                }
                $sqlAlbum = "SELECT * FROM freebox_video_album WHERE id=" .
                     $resultAlbumKw[0]['freebox_video_album_id'] . " ;";
                $resultAlbum = $this->db->fetchAll($sqlAlbum);
                $sqlVideo = "SELECT * FROM freebox_video WHERE freebox_video_album_id=? ;";
                $resultVideo = $this->db->fetchAll($sqlVideo, 
                    array(
                        (int) $resultAlbum[0]['id']
                    ));
                foreach ($resultVideo as $video) {
                    $this->insertMediaPlayList($type, $deviceId, $video['id']);
                }
            } elseif ($type == 'photo') {
                $sqlAlbumKw = "SELECT DISTINCT freebox_photo_album_id FROM freebox_photo_album_kw WHERE UPPER(name)='" .
                     strtoupper(str_replace('%20', ' ', $elementName)) . "' ;";
                $resultAlbumKw = $this->db->fetchAll($sqlAlbumKw);
                if (count($resultAlbumKw) == 0) {
                    throw new \Exception(
                        "Pas de correspondance pour la photo " . $elementName . " !");
                }
                $sqlAlbum = "SELECT * FROM freebox_photo_album WHERE id=" .
                     $resultAlbumKw[0]['freebox_photo_album_id'] . " ;";
                $resultAlbum = $this->db->fetchAll($sqlAlbum);
                $sqlPhoto = "SELECT * FROM freebox_photo WHERE freebox_photo_album_id=? ;";
                $resultPhoto = $this->db->fetchAll($sqlPhoto, 
                    array(
                        (int) $resultAlbum[0]['id']
                    ));
                foreach ($resultPhoto as $photo) {
                    $this->insertMediaPlayList($type, $deviceId, $photo['id']);
                }
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function addMediaItemByName($type, $elementName, $deviceId) {
        try {
            if ($type == 'audio') {
                $sqlMusicKw = "SELECT DISTINCT freebox_music_id FROM freebox_music_kw WHERE UPPER(name)='" .
                     strtoupper(str_replace('%20', ' ', $elementName)) . "' ;";
                $resultMusicKw = $this->db->fetchAll($sqlMusicKw);
                if (count($resultMusicKw) == 0) {
                    throw new \Exception(
                        "Pas de correspondance pour le titre " . $elementName . " !");
                }
                $sqlMusic = "SELECT * FROM freebox_music WHERE id=? ;";
                $resultMusic = $this->db->fetchAssoc($sqlMusic, 
                    array(
                        (int) $resultMusicKw[0]['freebox_music_id']
                    ));
                $this->insertMediaPlayList($type, $deviceId, $resultMusic['id']);
            } elseif ($type == 'video') {
                $sqlVideoKw = "SELECT DISTINCT freebox_video_id FROM freebox_video_kw WHERE UPPER(name)='" .
                     strtoupper(str_replace('%20', ' ', $elementName)) . "' ;";
                $resultVideoKw = $this->db->fetchAll($sqlVideoKw);
                if (count($resultVideoKw) == 0) {
                    throw new \Exception(
                        "Pas de correspondance pour la vidéo " . $elementName . " !");
                }
                $sqlVideo = "SELECT * FROM freebox_video WHERE id=? ;";
                $resultVideo = $this->db->fetchAssoc($sqlVideo, 
                    array(
                        (int) $resultVideoKw[0]['freebox_video_id']
                    ));
                $this->insertMediaPlayList($type, $deviceId, $resultVideo['id']);
            } elseif ($type == 'photo') {
                $sqlPhotoKw = "SELECT DISTINCT freebox_photo_id FROM freebox_photo_kw WHERE UPPER(name)='" .
                     strtoupper(str_replace('%20', ' ', $elementName)) . "' ;";
                $resultPhotoKw = $this->db->fetchAll($sqlPhotoKw);
                if (count($resultPhotoKw) == 0) {
                    throw new \Exception(
                        "Pas de correspondance pour la photo " . $elementName . " !");
                }
                $sqlPhoto = "SELECT * FROM freebox_photo WHERE id=? ;";
                $resultPhoto = $this->db->fetchAssoc($sqlPhoto, 
                    array(
                        (int) $resultPhotoKw[0]['freebox_photo_id']
                    ));
                $this->insertMediaPlayList($type, $deviceId, $resultPhoto['id']);
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function launchPlaylist($deviceId) {
        try {
            exec(
                '/usr/local/zend/bin/php /var/www/html/domoapi/app/console playlist:launch ' . $deviceId);
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function startPlayer($deviceId) {
        try {
            $sqlStop = "UPDATE device_player_activity SET start=1, pause=0, stop=0" .
                 " WHERE freebox_device_player_id=" . $deviceId . " ;";
            $this->db->exec($sqlStop);
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function pausePlayer($deviceId) {
        try {
            $sqlStop = "UPDATE device_player_activity SET pause=1, stop=0" .
                 " WHERE freebox_device_player_id=" . $deviceId . " ;";
            $this->db->exec($sqlStop);
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function stopPlayer($deviceId) {
        try {
            $sqlStop = "UPDATE device_player_activity SET start=0, pause=0, stop=1" .
                 " WHERE freebox_device_player_id=" . $deviceId . " ;";
            $this->db->exec($sqlStop);
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getPlaylistActivity($deviceId) {
        try {
            $sqlActivity = "SELECT * FROM device_player_activity WHERE freebox_device_player_id=" .
                 $deviceId . " ;";
            $result = $this->db->fetchAll($sqlActivity);
            ;
            return $result[0];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function emptyPlaylist($deviceId) {
        try {
            $sql = "DELETE FROM freebox_receivers_queue WHERE freebox_device_player_id=" . $deviceId .
                 ";";
            $result = $this->db->exec($sql);
            if (! $result) {
                $this->log->addError("Erreur lors de la purge de la playlist !");
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getPlaylist($deviceId) {
        try {
            $sql = "SELECT * FROM freebox_receivers_queue WHERE freebox_device_player_id=" .
                 $deviceId . ";";
            return $this->db->fetchAll($sql);
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getMediaPlaylistById($mediaId) {
        try {
            $sql = "SELECT * FROM freebox_receivers_queue WHERE id=?;";
            $result = $this->db->fetchAssoc($sql, 
                array(
                    (int) $mediaId
                ));
            return $result;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getCurrentElementPlaylist($deviceId) {
        try {
            $sqlCurrent = "SELECT * FROM freebox_receivers_queue " .
                 "WHERE freebox_device_player_id=" . $deviceId . " AND current=1;";
            $result = $this->db->fetchAll($sqlCurrent);
            if (count($result) == 0) {
                $sqlNext = "SELECT * FROM freebox_receivers_queue " .
                     "WHERE freebox_device_player_id=" . $deviceId . " ORDER BY id ASC;";
                $result = $this->db->fetchAll($sqlNext);
            }
            $element = array();
            if (count($result) > 0) {
                if ($result[0]['freebox_music_id']) {
                    $element['type'] = 'audio';
                    $sqlMusic = "SELECT * FROM freebox_music WHERE id=" .
                         $result[0]['freebox_music_id'] . " ;";
                    $resultMusic = $this->db->fetchAll($sqlMusic);
                    $sqlAlbum = "SELECT * FROM freebox_music_album WHERE id=? ;";
                    $resultAlbum = $this->db->fetchAssoc($sqlAlbum, 
                        array(
                            (int) $resultMusic[0]['freebox_music_album_id']
                        ));
                    $element['url'] = $resultAlbum['url'] . $resultMusic[0]['realname'];
                } elseif ($result[0]['freebox_video_id']) {
                    $element['type'] = 'video';
                    $sqlVideo = "SELECT * FROM freebox_video WHERE id=" .
                         $result[0]['freebox_video_id'] . " ;";
                    $resultVideo = $this->db->fetchAll($sqlVideo);
                    $sqlAlbum = "SELECT * FROM freebox_video_album WHERE id=? ;";
                    $resultAlbum = $this->db->fetchAssoc($sqlAlbum, 
                        array(
                            (int) $resultVideo[0]['freebox_video_album_id']
                        ));
                    $element['url'] = $resultAlbum['url'] . $resultVideo[0]['realname'];
                } elseif ($result[0]['freebox_photo_id']) {
                    $element['type'] = 'photo';
                    $sqlPhoto = "SELECT * FROM freebox_photo WHERE id=" .
                         $result[0]['freebox_photo_id'] . " ;";
                    $resultPhoto = $this->db->fetchAll($sqlPhoto);
                    $sqlAlbum = "SELECT * FROM freebox_photo_album WHERE id=? ;";
                    $resultAlbum = $this->db->fetchAssoc($sqlAlbum, 
                        array(
                            (int) $resultPhoto[0]['freebox_photo_album_id']
                        ));
                    $element['url'] = $resultAlbum['url'] . $resultPhoto[0]['realname'];
                }
                $sqlCurrent = "UPDATE freebox_receivers_queue " . "SET current=1, timestamp_start=" .
                     time() . " WHERE freebox_device_player_id=" . $deviceId . " AND id=" .
                     $result[0]['id'] . " ;";
                $result = $this->db->exec($sqlCurrent);
            }
            return $element;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getMediaPlaylist($media) {
        try {
            $element = array();
            if ($media['freebox_music_id']) {
                $element['type'] = 'audio';
                $sqlMusic = "SELECT * FROM freebox_music WHERE id=" . $media['freebox_music_id'] .
                     " ;";
                $resultMusic = $this->db->fetchAll($sqlMusic);
                $element['length'] = $resultMusic[0]['duration'];
                $sqlAlbum = "SELECT * FROM freebox_music_album WHERE id=? ;";
                $resultAlbum = $this->db->fetchAssoc($sqlAlbum, 
                    array(
                        (int) $resultMusic[0]['freebox_music_album_id']
                    ));
                $element['url'] = $resultAlbum['url'] . $resultMusic[0]['realname'];
            } elseif ($media['freebox_video_id']) {
                $element['type'] = 'video';
                $sqlVideo = "SELECT * FROM freebox_video WHERE id=" . $media['freebox_video_id'] .
                     " ;";
                $resultVideo = $this->db->fetchAll($sqlVideo);
                $element['length'] = $resultVideo[0]['duration'];
                $sqlAlbum = "SELECT * FROM freebox_video_album WHERE id=? ;";
                $resultAlbum = $this->db->fetchAssoc($sqlAlbum, 
                    array(
                        (int) $resultVideo[0]['freebox_video_album_id']
                    ));
                $element['url'] = $resultAlbum['url'] . $resultVideo[0]['realname'];
            } elseif ($media['freebox_photo_id']) {
                $element['type'] = 'photo';
                $sqlPhoto = "SELECT * FROM freebox_photo WHERE id=" . $media['freebox_photo_id'] .
                     " ;";
                $resultPhoto = $this->db->fetchAll($sqlPhoto);
                $sqlAlbum = "SELECT * FROM freebox_photo_album WHERE id=? ;";
                $resultAlbum = $this->db->fetchAssoc($sqlAlbum, 
                    array(
                        (int) $resultPhoto[0]['freebox_photo_album_id']
                    ));
                $element['url'] = $resultAlbum['url'] . $resultPhoto[0]['realname'];
            }
            $timestampStart = time();
            $element['timestampStart'] = $timestampStart;
            return $element;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function setCurrentMediaPlaylist($deviceId, $mediaId) {
        try {
            $sqlLast = "UPDATE freebox_receivers_queue SET current=0, pause=0, " .
                 "timestamp_start=0 WHERE current=1 AND freebox_device_player_id=" . $deviceId . " ;";
            $this->db->exec($sqlLast);
            $sqlCurrent = "UPDATE freebox_receivers_queue SET current=1, pause=0, " .
                 "timestamp_start=" . time() . " WHERE  id=" . $mediaId . " ;";
            $this->db->exec($sqlCurrent);
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function setNextMediaPlaylist($deviceId, $mediaId) {
        try {
            $sqlLast = "UPDATE freebox_receivers_queue SET current=0, pause=0, " .
                 "timestamp_start=0 WHERE current=1 AND freebox_device_player_id=" . $deviceId . " ;";
            $this->db->exec($sqlLast);
            $sqlNext = "SELECT * FROM freebox_receivers_queue WHERE freebox_device_player_id=" .
                 $deviceId . " AND id > " . $mediaId . " ORDER BY id ASC ;";
            $resultNext = $this->db->fetchAll($sqlNext);
            if (count($resultNext) == 0) {
                $sqlNext2 = "SELECT * FROM freebox_receivers_queue WHERE freebox_device_player_id=" .
                     $deviceId . " ORDER BY id ASC ;";
                $resultNext = $this->db->fetchAll($sqlNext2);
            }
            if (count($resultNext) == 0) {
                throw new \Exception("Aucun élément dans la playlist.");
            }
            $sqlCurrent = "UPDATE freebox_receivers_queue SET current=1, pause=0, " .
                 "timestamp_start=" . time() . " WHERE  id=" . $resultNext[0]['id'] . " ;";
            $this->db->exec($sqlCurrent);
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function setLastMediaPlaylist($deviceId, $mediaId) {
        try {
            $sqlLast = "UPDATE freebox_receivers_queue SET current=0, pause=0, " .
                 "timestamp_start=0 WHERE current=1 AND freebox_device_player_id=" . $deviceId . " ;";
            $this->db->exec($sqlLast);
            $sqlLast2 = "SELECT * FROM freebox_receivers_queue WHERE freebox_device_player_id=" .
                 $deviceId . " AND id < " . $mediaId . " ORDER BY id DESC ;";
            $resultLast = $this->db->fetchAll($sqlLast2);
            if (count($resultLast) == 0) {
                $sqlLast3 = "SELECT * FROM freebox_receivers_queue WHERE freebox_device_player_id=" .
                     $deviceId . " ORDER BY id DESC ;";
                $resultLast = $this->db->fetchAll($sqlLast3);
            }
            if (count($resultLast) == 0) {
                throw new \Exception("Aucun élément dans la playlist.");
            }
            $sqlCurrent = "UPDATE freebox_receivers_queue SET current=1, pause=0, " .
                 "timestamp_start=" . time() . " WHERE  id=" . $resultLast[0]['id'] . " ;";
            $this->db->exec($sqlCurrent);
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getCurrentMediaPlaylist($deviceId) {
        try {
            $sqlCheckCurrent = "SELECT * FROM freebox_receivers_queue " .
                 "WHERE freebox_device_player_id=" . $deviceId . " AND current=1;";
            $resultCheckCurrent = $this->db->fetchAll($sqlCheckCurrent);
            if (count($resultCheckCurrent) == 0) {
                throw new \Exception("Aucun élément n'est en lecture !");
            }
            $sqlCurrent = "SELECT * FROM freebox_receivers_queue WHERE current=1 AND freebox_device_player_id=" .
                 $deviceId . " ;";
            $resultCurrent = $this->db->fetchAll($sqlCurrent);
            return $resultCurrent[0];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getNextMediaPlaylist($deviceId) {
        try {
            $sqlCurrent = "SELECT * FROM freebox_receivers_queue " .
                 "WHERE freebox_device_player_id=" . $deviceId . " AND current=1;";
            $resultCurrent = $this->db->fetchAll($sqlCurrent);
            if (count($resultCurrent) == 0) {
                $sqlNext = "SELECT * FROM freebox_receivers_queue " .
                     "WHERE freebox_device_player_id=" . $deviceId . " ORDER BY id ASC ;";
                $resultNext = $this->db->fetchAll($sqlNext);
            } else {
                $sqlNext = "SELECT * FROM freebox_receivers_queue " .
                     "WHERE freebox_device_player_id=" . $deviceId . " AND id>" .
                     $resultCurrent[0]['id'] . " ORDER BY id ASC ;";
                $resultNext = $this->db->fetchAll($sqlNext);
            }
            if (count($resultNext) == 0) {
                throw new \Exception("Plus d'élément dans la playlist!");
            }
            return $resultNext[0];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getLastMediaPlaylist($deviceId) {
        try {
            $sqlCurrent = "SELECT * FROM freebox_receivers_queue " .
                 "WHERE freebox_device_player_id=" . $deviceId . " AND current=1;";
            $resultCurrent = $this->db->fetchAll($sqlCurrent);
            if (count($resultCurrent) == 0) {
                $sqlLast = "SELECT * FROM freebox_receivers_queue " .
                     "WHERE freebox_device_player_id=" . $deviceId . " ORDER BY id ASC ;";
                $resultLast = $this->db->fetchAll($sqlLast);
            } else {
                $sqlLast = "SELECT * FROM freebox_receivers_queue " .
                     "WHERE freebox_device_player_id=" . $deviceId . " AND id<" .
                     $resultCurrent[0]['id'] . " ORDER BY id DESC ;";
                $resultLast = $this->db->fetchAll($sqlLast);
            }
            if (count($resultLast) == 0) {
                throw new \Exception("Il s'agissait du premier élément de la playlist!");
            }
            return $resultLast[0];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getNextElementPlaylist($deviceId) {
        try {
            $sqlCurrent = "SELECT * FROM freebox_receivers_queue " .
                 "WHERE freebox_device_player_id=" . $deviceId . " AND current=1;";
            $resultCurrent = $this->db->fetchAll($sqlCurrent);
            if (count($resultCurrent) == 0) {
                throw new \Exception("Aucun élément n'est en lecture !");
            }
            $sqlNext = "SELECT * FROM freebox_receivers_queue " . "WHERE freebox_device_player_id=" .
                 $deviceId . " AND id>" . $resultCurrent[0]['id'] . " ORDER BY id ASC ;";
            $resultNext = $this->db->fetchAll($sqlNext);
            if (count($resultNext) == 0) {
                throw new \Exception("Plus d'élément dans la playlist!");
            }
            $element = array();
            if ($resultNext[0]['freebox_music_id']) {
                $element['type'] = 'audio';
                $sqlMusic = "SELECT * FROM freebox_music WHERE id=" .
                     $resultNext[0]['freebox_music_id'] . " ;";
                $resultMusic = $this->db->fetchAll($sqlMusic);
                $sqlAlbum = "SELECT * FROM freebox_music_album WHERE id=? ;";
                $resultAlbum = $this->db->fetchAssoc($sqlAlbum, 
                    array(
                        (int) $resultMusic[0]['freebox_music_album_id']
                    ));
                $element['url'] = $resultAlbum['url'] . $resultMusic[0]['realname'];
            } elseif ($resultNext[0]['freebox_video_id']) {
                $element['type'] = 'video';
                $sqlVideo = "SELECT * FROM freebox_video WHERE id=" .
                     $resultNext[0]['freebox_video_id'] . " ;";
                $resultVideo = $this->db->fetchAll($sqlVideo);
                $sqlAlbum = "SELECT * FROM freebox_video_album WHERE id=? ;";
                $resultAlbum = $this->db->fetchAssoc($sqlAlbum, 
                    array(
                        (int) $resultVideo[0]['freebox_video_album_id']
                    ));
                $element['url'] = $resultAlbum['url'] . $resultVideo[0]['realname'];
            } elseif ($resultNext[0]['freebox_photo_id']) {
                $element['type'] = 'photo';
                $sqlPhoto = "SELECT * FROM freebox_photo WHERE id=" .
                     $resultNext[0]['freebox_photo_id'] . " ;";
                $resultPhoto = $this->db->fetchAll($sqlPhoto);
                $sqlAlbum = "SELECT * FROM freebox_photo_album WHERE id=? ;";
                $resultAlbum = $this->db->fetchAssoc($sqlAlbum, 
                    array(
                        (int) $resultPhoto[0]['freebox_photo_album_id']
                    ));
                $element['url'] = $resultAlbum['url'] . $resultPhoto[0]['realname'];
            }
            $sqlCurrent = "UPDATE freebox_receivers_queue " .
                 "SET current=0 WHERE freebox_device_player_id=" . $deviceId . " AND id=" .
                 $resultCurrent[0]['id'] . " ;";
            $this->db->exec($sqlCurrent);
            $sqlNext = "UPDATE freebox_receivers_queue " . "SET current=1, timestamp_start=" . time() .
                 " WHERE freebox_device_player_id=" . $deviceId . " AND id=" . $resultNext[0]['id'] .
                 " ;";
            $this->db->exec($sqlNext);
            return $element;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getLastElementPlaylist($deviceId) {
        try {
            $sqlCurrent = "SELECT * FROM freebox_receivers_queue " .
                 "WHERE freebox_device_player_id=" . $deviceId . " AND current=1;";
            $resultCurrent = $this->db->fetchAll($sqlCurrent);
            if (count($resultCurrent) == 0) {
                throw new \Exception("Aucun élément n'est en lecture !");
            }
            $sqlLast = "SELECT * FROM freebox_receivers_queue " . "WHERE freebox_device_player_id=" .
                 $deviceId . " AND id<" . $resultCurrent[0]['id'] . " ORDER BY id DESC ;";
            $resultLast = $this->db->fetchAll($sqlLast);
            if (count($resultLast) == 0) {
                throw new \Exception("Il s'agissait du premier élément de la playlist!");
            }
            $element = array();
            if ($resultLast[0]['freebox_music_id']) {
                $element['type'] = 'audio';
                $sqlMusic = "SELECT * FROM freebox_music WHERE id=" .
                     $resultLast[0]['freebox_music_id'] . " ;";
                $resultMusic = $this->db->fetchAll($sqlMusic);
                $sqlAlbum = "SELECT * FROM freebox_music_album WHERE id=? ;";
                $resultAlbum = $this->db->fetchAssoc($sqlAlbum, 
                    array(
                        (int) $resultMusic[0]['freebox_music_album_id']
                    ));
                $element['url'] = $resultAlbum['url'] . $resultMusic[0]['realname'];
            } elseif ($resultLast[0]['freebox_video_id']) {
                $element['type'] = 'video';
                $sqlVideo = "SELECT * FROM freebox_video WHERE id=" .
                     $resultLast[0]['freebox_video_id'] . " ;";
                $resultVideo = $this->db->fetchAll($sqlVideo);
                $sqlAlbum = "SELECT * FROM freebox_video_album WHERE id=? ;";
                $resultAlbum = $this->db->fetchAssoc($sqlAlbum, 
                    array(
                        (int) $resultVideo[0]['freebox_video_album_id']
                    ));
                $element['url'] = $resultAlbum['url'] . $resultVideo[0]['realname'];
            } elseif ($resultLast[0]['freebox_photo_id']) {
                $element['type'] = 'photo';
                $sqlPhoto = "SELECT * FROM freebox_photo WHERE id=" .
                     $resultLast[0]['freebox_photo_id'] . " ;";
                $resultPhoto = $this->db->fetchAll($sqlPhoto);
                $sqlAlbum = "SELECT * FROM freebox_photo_album WHERE id=? ;";
                $resultAlbum = $this->db->fetchAssoc($sqlAlbum, 
                    array(
                        (int) $resultPhoto[0]['freebox_photo_album_id']
                    ));
                $element['url'] = $resultAlbum['url'] . $resultPhoto[0]['realname'];
            }
            $sqlCurrent = "UPDATE freebox_receivers_queue " .
                 "SET current=0 WHERE freebox_device_player_id=" . $deviceId . " AND id=" .
                 $resultCurrent[0]['id'] . " ;";
            $this->db->exec($sqlCurrent);
            $sqlLast = "UPDATE freebox_receivers_queue " . "SET current=1, timestamp_start=" . time() .
                 " WHERE freebox_device_player_id=" . $deviceId . " AND id=" . $resultLast[0]['id'] .
                 " ;";
            $this->db->exec($sqlLast);
            return $element;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function pausePlaylist($deviceId) {
        try {
            $sqlSelect = "SELECT * FROM freebox_receivers_queue WHERE freebox_device_player_id=" .
                 $deviceId . " AND current=1;";
            $resultSelect = $this->db->fetchAll($sqlSelect);
            $sql = "UPDATE freebox_receivers_queue SET timestamp_pause=" . time() .
                 ", pause=1 WHERE id=" . $resultSelect[0]['id'] . " ;";
            $result = $this->db->exec($sql);
            if (! $result) {
                $this->log->addError("Erreur lors de la mise en pause de la playlist !");
            }
            return $resultSelect[0]['id'];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function resumePlaylist($deviceId) {
        try {
            $sqlSelect = "SELECT * FROM freebox_receivers_queue WHERE freebox_device_player_id=" .
                 $deviceId . " AND current=1;";
            $resultSelect = $this->db->fetchAll($sqlSelect);
            $mediaSelect = $resultSelect[0];
            if ($mediaSelect['freebox_music_id']) {
                $type = 'audio';
                $sqlMusic = "SELECT * FROM freebox_music WHERE id=" .
                     $mediaSelect['freebox_music_id'] . " ;";
                $resultMusic = $this->db->fetchAll($sqlMusic);
                $sqlAlbum = "SELECT * FROM freebox_music_album WHERE id=? ;";
                $resultAlbum = $this->db->fetchAssoc($sqlAlbum, 
                    array(
                        (int) $resultMusic[0]['freebox_music_album_id']
                    ));
                $url = $resultAlbum['url'] . $resultMusic[0]['realname'];
                $duration = $resultMusic[0]['duration'];
            } elseif ($mediaSelect['freebox_video_id']) {
                $type = 'video';
                $sqlVideo = "SELECT * FROM freebox_video WHERE id=" .
                     $mediaSelect['freebox_video_id'] . " ;";
                $resultVideo = $this->db->fetchAll($sqlVideo);
                $sqlAlbum = "SELECT * FROM freebox_video_album WHERE id=? ;";
                $resultAlbum = $this->db->fetchAssoc($sqlAlbum, 
                    array(
                        (int) $resultVideo[0]['freebox_video_album_id']
                    ));
                $url = $resultAlbum['url'] . $resultVideo[0]['realname'];
                $duration = $resultVideo[0]['duration'];
            }
            $timeElapsed = $mediaSelect['timestamp_pause'] - $mediaSelect['timestamp_start'];
            $position = ($timeElapsed / $duration) * 100 * 1000;
            $newTimestampStart = time() - $timeElapsed;
            $sql = "UPDATE freebox_receivers_queue SET timestamp_start=" . $newTimestampStart .
                 ", timestamp_pause=0" . ", pause=0 WHERE id=" . $mediaSelect['id'] . " ;";
            $result = $this->db->exec($sql);
            if (! $result) {
                $this->log->addError("Erreur lors de la reprise de la playlist !");
            }
            $infoResume = array(
                "id" => $mediaSelect['id'],
                "position" => $position,
                "url" => $url,
                "type" => $type
            );
            return $infoResume;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function createOrGetGenre($genre) {
        try {
            $sqlSelect = "SELECT * FROM freebox_music_type WHERE UPPER(name)='" .
                 strtoupper(str_replace("'", "", $genre)) . "' ;";
            $resultSelect = $this->db->fetchAll($sqlSelect);
            if (count($resultSelect) == 0) {
                $sqlInsert = "INSERT INTO freebox_music_type (name) VALUES ('" .
                     str_replace("'", "", $genre) . "') ;";
                $this->db->exec($sqlInsert);
                $id = $this->db->lastInsertId();
            } else {
                $id = $resultSelect[0]['id'];
            }
            return $id;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function createGenreKw($genreId, $name) {
        try {
            $sqlInsert = "INSERT INTO freebox_music_type_kw (freebox_music_type_id, name) VALUES (" .
                 $genreId . ", '" . str_replace("'", "", $name) . "') ;";
            $this->db->exec($sqlInsert);
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function createOrGetArtist($name, $typeId) {
        try {
            $sqlTypeSelect = "";
            $sqlTypeInsert = "";
            $valueTypeInsert = "";
            if ($typeId > 0) {
                $sqlTypeSelect = " AND freebox_music_type_id = " . $typeId;
                $sqlTypeInsert = "freebox_music_type_id,";
                $valueTypeInsert = $typeId . ",";
            }
            $sqlSelect = "SELECT * FROM freebox_music_artist WHERE UPPER(name)='" .
                 strtoupper(str_replace("'", "", $name)) . "'" . $sqlTypeSelect . " ;";
            $resultSelect = $this->db->fetchAll($sqlSelect);
            if (count($resultSelect) == 0) {
                $sqlInsert = "INSERT INTO freebox_music_artist (" . $sqlTypeInsert . "name) VALUES (" .
                     $valueTypeInsert . "'" . str_replace("'", "", $name) . "') ;";
                $this->db->exec($sqlInsert);
                $id = $this->db->lastInsertId();
                $sqlSelect = "SELECT * FROM freebox_music_artist WHERE id=" . $id . " ;";
                $resultSelect = $this->db->fetchAll($sqlSelect);
            }
            return $resultSelect[0];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function createArtistKw($artistId, $name) {
        try {
            $sqlInsert = "INSERT INTO freebox_music_artist_kw (freebox_music_artist_id, name) VALUES (" .
                 $artistId . ", '" . str_replace("'", "", $name) . "') ;";
            $this->db->exec($sqlInsert);
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function checkAlbum($name, $realname, $artistId, $typeId) {
        try {
            $sqlTypeSelect = "";
            if ($typeId > 0) {
                $sqlTypeSelect = " AND freebox_music_type_id = " . $typeId;
            }
            $sqlSelect = "SELECT * FROM freebox_music_album WHERE UPPER(name)='" .
                 strtoupper(str_replace("'", "", $name)) . "' AND UPPER(realname)='" .
                 strtoupper(str_replace("'", "", $realname)) . "' AND freebox_music_artist_id=" .
                 $artistId . $sqlTypeSelect . ";";
            $resultSelect = $this->db->fetchAll($sqlSelect);
            if (count($resultSelect) == 0) {
                return false;
            }
            return $resultSelect[0];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function createAlbum($name, $realname, $artistId, $typeId, $url) {
        try {
            $sqlTypeInsert = "";
            $valueTypeInsert = "";
            if ($typeId > 0) {
                $sqlTypeInsert = "freebox_music_type_id,";
                $valueTypeInsert = $typeId . ",";
            }
            $sqlInsert = "INSERT INTO freebox_music_album (freebox_music_artist_id, " .
                 $sqlTypeInsert . "name, realname, url) VALUES (" . $artistId . "," .
                 $valueTypeInsert . "'" . str_replace("'", "", $name) . "','" .
                 str_replace("'", "", $realname) . "', '" . $url . "') ;";
            $this->db->exec($sqlInsert);
            $id = $this->db->lastInsertId();
            $sqlSelect = "SELECT * FROM freebox_music_album WHERE id=" . $id . ";";
            $resultSelect = $this->db->fetchAll($sqlSelect);
            return $resultSelect[0];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function createAlbumKw($albumId, $name) {
        try {
            $sqlInsert = "INSERT INTO freebox_music_album_kw (freebox_music_album_id, name) VALUES (" .
                 $albumId . ", '" . str_replace("'", "", $name) . "') ;";
            $this->db->exec($sqlInsert);
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function checkMusic($name, $albumId, $artistId, $typeId) {
        try {
            $sqlTypeSelect = "";
            if ($typeId > 0) {
                $sqlTypeSelect = " AND freebox_music_type_id = " . $typeId;
            }
            $sqlSelect = "SELECT * FROM freebox_music WHERE UPPER(name)='" .
                 strtoupper(str_replace("'", "", $name)) . "' AND freebox_music_album_id=" . $albumId .
                 " AND freebox_music_artist_id=" . $artistId . $sqlTypeSelect . ";";
            $resultSelect = $this->db->fetchAll($sqlSelect);
            if (count($resultSelect) == 0) {
                return false;
            }
            return $resultSelect[0];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function createMusicKw($musicId, $name) {
        try {
            $sqlInsert = "INSERT INTO freebox_music_kw (freebox_music_id, name) VALUES (" . $musicId .
                 ", '" . str_replace("'", "", $name) . "') ;";
            $this->db->exec($sqlInsert);
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function createMusic($name, $albumId, $artistId, $typeId, $filename, $duration) {
        try {
            $sqlTypeInsert = "";
            $valueTypeInsert = "";
            if ($typeId > 0) {
                $sqlTypeInsert = "freebox_music_type_id,";
                $valueTypeInsert = $typeId . ",";
            }
            $sqlInsert = "INSERT INTO freebox_music (freebox_music_album_id, freebox_music_artist_id, " .
                 $sqlTypeInsert . "name, realname, duration) VALUES (" . $albumId . "," . $artistId .
                 "," . $valueTypeInsert . "'" . str_replace("'", "", $name) . "','" . $filename .
                 "','" . $duration . "') ;";
            $this->db->exec($sqlInsert);
            $id = $this->db->lastInsertId();
            $sqlSelect = "SELECT * FROM freebox_music WHERE id=" . $id . ";";
            $resultSelect = $this->db->fetchAll($sqlSelect);
            return $resultSelect[0];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function createOrGetAlbum($name, $artistId, $typeId, $url) {
        try {
            $sqlTypeSelect = "";
            $sqlTypeInsert = "";
            $valueTypeInsert = "";
            if ($typeId > 0) {
                $sqlTypeSelect = " AND freebox_music_type_id = " . $typeId;
                $sqlTypeInsert = "'freebox_music_type_id',";
                $valueTypeInsert = $typeId . ",";
            }
            $sqlSelect = "SELECT * FROM freebox_music_album WHERE name='" .
                 str_replace("'", "", $name) . "' AND  freebox_music_artist=" . $artistId .
                 $sqlTypeSelect . ";";
            $resultSelect = $this->db->fetchAll($sqlSelect);
            if (count($resultSelect) == 0) {
                $sqlInsert = "INSERT INTO freebox_music_album (" . $sqlTypeInsert . "name) VALUES (" .
                     $valueTypeInsert . "'" . str_replace("'", "", $name) . "') ;";
                $this->db->exec($sqlInsert);
                $id = $this->db->lastInsertId();
                $sqlSelect = "SELECT * FROM freebox_music_artist WHERE id=" . $id . " ;";
                $resultSelect = $this->db->fetchAll($sqlSelect);
            }
            return $resultSelect[0];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getAllVideoType() {
        try {
            $sqlSelect = "SELECT * FROM freebox_video_type ;";
            $resultSelect = $this->db->fetchAll($sqlSelect);
            return $resultSelect;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getVideoTypeByPath($path) {
        try {
            $sqlSelect = "SELECT * FROM freebox_video_type WHERE path='" . $path . "' ;";
            $resultType = $this->db->fetchAll($sqlSelect);
            if (count($resultType) > 0) {
                return $resultType[0];
            }
            return false;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getVideoQualityByPath($path) {
        try {
            $sqlSelect = "SELECT * FROM freebox_video_quality WHERE path='" . $path . "' ;";
            $resultQuality = $this->db->fetchAll($sqlSelect);
            if (count($resultQuality) > 0) {
                return $resultQuality[0];
            }
            return false;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getAllVideoCategoryPath() {
        try {
            $videoPathList = array();
            $sqlCategory = "SELECT * FROM freebox_video_category WHERE freebox_video_category_id is null;";
            $resultCategory = $this->db->fetchAll($sqlCategory);
            foreach ($resultCategory as $category) {
                $videoPath = array();
                $sqlType = "SELECT * FROM freebox_video_type WHERE id=? ;";
                $resultType = $this->db->fetchAssoc($sqlType, 
                    array(
                        (int) $category['freebox_video_type_id']
                    ));
                $videoPath[] = $resultType['path'];
                
                $sqlSubcategory = "SELECT * FROM freebox_video_category WHERE freebox_video_category_id=" .
                     $category['id'] . " ;";
                $resultSubcategory = $this->db->fetchAll($sqlSubcategory);
                if (count($resultSubcategory) > 0) {
                    foreach ($resultSubcategory as $subcategory) {
                        $videoSubcategoryPath = $videoPath;
                        if ($subcategory['freebox_video_type_id']) {
                            $sqlTypeSub = "SELECT * FROM freebox_video_type WHERE id=? ;";
                            $resultTypeSub = $this->db->fetchAssoc($sqlTypeSub, 
                                array(
                                    (int) $subcategory['freebox_video_type_id']
                                ));
                            $videoSubcategoryPath[] = $resultTypeSub['path'];
                        }
                        if ($subcategory['freebox_video_quality_id']) {
                            $sqlQuality = "SELECT * FROM freebox_video_quality WHERE id=? ;";
                            $resultQuality = $this->db->fetchAssoc($sqlQuality, 
                                array(
                                    (int) $subcategory['freebox_video_quality_id']
                                ));
                            $videoSubcategoryPath[] = $resultQuality['path'];
                        }
                        $videoPathList[] = array(
                            "categoryId" => $subcategory['id'],
                            "path" => $videoSubcategoryPath
                        );
                    }
                } else {
                    if ($category['freebox_video_quality_id']) {
                        $sqlQuality = "SELECT * FROM freebox_video_quality WHERE id=? ;";
                        $resultQuality = $this->db->fetchAssoc($sqlQuality, 
                            array(
                                (int) $category['freebox_video_quality_id']
                            ));
                        $videoPath[] = $resultQuality['path'];
                    }
                    $videoPathList[] = array(
                        "categoryId" => $category['id'],
                        "path" => $videoPath
                    );
                }
            }
            return $videoPathList;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getVideoCategoryPath($id) {
        try {
            $sqlCategory = "SELECT * FROM freebox_video_category WHERE id=? ;";
            $category = $this->db->fetchAssoc($sqlCategory, 
                array(
                    (int) $id
                ));
            $videoPath = array();
            if ($category['freebox_video_category_id']) {
                $sqlHighCategory = "SELECT * FROM freebox_video_category WHERE id=? ;";
                $highCategory = $this->db->fetchAssoc($sqlHighCategory, 
                    array(
                        (int) $category['freebox_video_category_id']
                    ));
                $sqlType = "SELECT * FROM freebox_video_type WHERE id=? ;";
                $resultType = $this->db->fetchAssoc($sqlType, 
                    array(
                        (int) $highCategory['freebox_video_type_id']
                    ));
                $videoPath[] = $resultType['path'];
            }
            $sqlType = "SELECT * FROM freebox_video_type WHERE id=? ;";
            $resultType = $this->db->fetchAssoc($sqlType, 
                array(
                    (int) $category['freebox_video_type_id']
                ));
            $videoPath[] = $resultType['path'];
            
            if ($category['freebox_video_quality_id']) {
                $sqlQuality = "SELECT * FROM freebox_video_quality WHERE id=? ;";
                $resultQuality = $this->db->fetchAssoc($sqlQuality, 
                    array(
                        (int) $category['freebox_video_quality_id']
                    ));
                $videoPath[] = $resultQuality['path'];
            }
            return $videoPath;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function checkAlbumVideo($name) {
        try {
            $sqlSelect = "SELECT * FROM freebox_video_album WHERE UPPER(realname)='" .
                 strtoupper($name) . "' ;";
            $resultSelect = $this->db->fetchAll($sqlSelect);
            if (count($resultSelect) == 0) {
                return false;
            }
            return $resultSelect[0];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function createAlbumVideoKw($albumVideoId, $name) {
        try {
            $sqlInsert = "INSERT INTO freebox_video_album_kw (freebox_video_album_id, name) VALUES (" .
                 $albumVideoId . ", '" . str_replace("'", "", $name) . "') ;";
            $this->db->exec($sqlInsert);
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function createAlbumVideo($name, $url) {
        try {
            $sqlInsert = "INSERT INTO freebox_video_album (name, realname, url) VALUES ('" . $name .
                 "','" . $name . "','" . $url . "') ;";
            $this->db->exec($sqlInsert);
            $id = $this->db->lastInsertId();
            $sqlSelect = "SELECT * FROM freebox_video_album WHERE id=" . $id . ";";
            $resultSelect = $this->db->fetchAll($sqlSelect);
            return $resultSelect[0];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function checkVideo($categoryId, $albumId, $filename) {
        try {
            $sqlSelect = "SELECT * FROM freebox_video WHERE UPPER(realname)='" .
                 strtoupper($filename) . "' AND freebox_video_album_id=" . $albumId .
                 " AND freebox_video_category_id=" . $categoryId . ";";
            $resultSelect = $this->db->fetchAll($sqlSelect);
            if (count($resultSelect) == 0) {
                return false;
            }
            return $resultSelect[0];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function createVideoKw($musicId, $name) {
        try {
            $sqlInsert = "INSERT INTO freebox_video_kw (freebox_video_id, name) VALUES (" . $musicId .
                 ", '" . str_replace("'", "", $name) . "') ;";
            $this->db->exec($sqlInsert);
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function createVideo($categoryId, $albumId, $name, $realname, $duration = 0) {
        try {
            $sqlInsert = "INSERT INTO freebox_video (freebox_video_album_id, freebox_video_category_id, " .
                 "name, realname, duration) VALUES (" . $albumId . "," . $categoryId . ",'" . $name .
                 "','" . $realname . "', " . $duration . ") ;";
            $this->db->exec($sqlInsert);
            $id = $this->db->lastInsertId();
            $sqlSelect = "SELECT * FROM freebox_video WHERE id=" . $id . ";";
            $resultSelect = $this->db->fetchAll($sqlSelect);
            return $resultSelect[0];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function insertMediaPlayList($type, $deviceId, $elementId) {
        try {
            if ($type == 'audio') {
                $sql = "INSERT INTO freebox_receivers_queue " .
                     "(freebox_device_player_id, freebox_music_id) VALUES (" . $deviceId . ", " .
                     $elementId . ");";
            } elseif ($type == 'video') {
                $sql = "INSERT INTO freebox_receivers_queue " .
                     "(freebox_device_player_id, freebox_video_id) VALUES (" . $deviceId . ", " .
                     $elementId . ");";
            } elseif ($type == 'photo') {
                $sql = "INSERT INTO freebox_receivers_queue " .
                     "(freebox_device_player_id, freebox_photo_id) VALUES (" . $deviceId . ", " .
                     $elementId . ");";
            }
            $result = $this->db->exec($sql);
            if (! $result) {
                $this->log->addError("Erreur lors de l'ajout d'un titre dans la playlist !");
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}