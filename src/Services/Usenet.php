<?php
namespace DomoApi\Services;

class Usenet {
    private $db = null;
    private $dbLocal = null;

    public function __construct($dbLocal, $db) {
        $this->dbLocal = $dbLocal;
        $this->db = $db;
    }

    public function __destruct() {}

    public function getById($id) {
        try {
            $resultSelect = $this->getBinById($id);
            if (! is_array($resultSelect)) {
                throw new \Exception($resultSelect);
            }
            $timestamp = $resultSelect['timestamp'];
            $resultSelect['date'] = date("d M Y", $timestamp);
            return $resultSelect;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getBySubCategoryAndPeriod($subCategoryId, $timestamp1, $timestamp2) {
        try {
            $sql = "
	            SELECT id, title, filename, size, timestamp, urlInfo, resolution, 
                mediaLangue_id, (select value from MediaLangue where id=mediaLangue_id) as languageName,
                (select picture from MediaLangue where id=mediaLangue_id) as languagePicture,
                mediaType_id, (select value from MediaType where id=mediaType_id) as mediaTypeName
                FROM BinariesElement
				WHERE typeMediaSubCategory_id=? 
				AND timestamp between ? AND ?
				ORDER BY timestamp DESC;";
            $resultSelect = $this->db->fetchAll($sql, 
                array(
                    (int) $subCategoryId,
                    (int) $timestamp1,
                    (int) $timestamp2
                ));
            return $resultSelect;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getLastBySubCategory($subCategoryId, $limit = 10) {
        try {
            $sql = "
	            SELECT id, title, filename, size, timestamp, urlInfo, resolution, 
	            (select value from MediaLangue where id=mediaLangue_id) as languageName,
                (select picture from MediaLangue where id=mediaLangue_id) as languagePicture,
                mediaType_id, (select value from MediaType where id=mediaType_id) as mediaTypeName
                FROM BinariesElement
				WHERE typeMediaSubCategory_id=?
				ORDER BY timestamp DESC
                LIMIT $limit ;";
            $resultSelect = $this->db->fetchAll($sql, 
                array(
                    (int) $subCategoryId
                ));
            return $resultSelect;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getDayBySubCategory($subCategoryId, $timestamp) {
        try {
            $timestamp1 = $timestamp;
            $timestamp2 = $timestamp + 86399;
            $sql = "
	            SELECT id, title, filename, size, timestamp, urlInfo, resolution, 
                mediaLangue_id, (select value from MediaLangue where id=mediaLangue_id) as languageName,
                (select picture from MediaLangue where id=mediaLangue_id) as languagePicture,
                mediaType_id, (select value from MediaType where id=mediaType_id) as mediaTypeName
                FROM BinariesElement
				WHERE typeMediaSubCategory_id=? 
				AND timestamp between ? AND ?
				ORDER BY timestamp DESC;";
            $resultSelect = $this->db->fetchAll($sql, 
                array(
                    (int) $subCategoryId,
                    (int) $timestamp1,
                    (int) $timestamp2
                ));
            return $resultSelect;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getAllSubCategory() {
        try {
            $sql = "
	            SELECT id, name, typeMediaCategory_id,
                (select name from TypeMediaCategory where id=typeMediaCategory_id) as categoryName
                FROM TypeMediaSubCategory";
            $resultSelect = $this->db->fetchAll($sql);
            return $resultSelect;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getAllCategory() {
        try {
            $sql = "SELECT * FROM TypeMediaCategory where visible=1";
            $resultSelect = $this->db->fetchAll($sql);
            return $resultSelect;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getSubCategoryByCategory($id) {
        try {
            $sql = "SELECT id, name FROM TypeMediaSubCategory WHERE typeMediaCategory_id=" . $id;
            $resultSelect = $this->db->fetchAll($sql);
            return $resultSelect;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function getBinById($id) {
        try {
            $sql = "
            SELECT id, title, filename, size, timestamp, urlInfo, resolution,
            (select value from MediaLangue where id=mediaLangue_id) as languageName,
            (select picture from MediaLangue where id=mediaLangue_id) as languagePicture,
            mediaType_id, (select value from MediaType where id=mediaType_id) as mediaTypeName
            FROM BinariesElement
            WHERE id=? ;";
            $resultSelect = $this->db->fetchAssoc($sql, 
                array(
                    (int) $id
                ));
            return $resultSelect;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function getAllNzb() {
        try {
            $sql = "SELECT * FROM NzbSearch;";
            $resultSelect = $this->db->fetchAll($sql);
            return $resultSelect;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getUrlNzb($id) {
        try {
            $sql = "select * from nzb WHERE id=? ;";
            $resultSelect = $this->dbLocal->fetchAssoc($sql, 
                array(
                    (int) $id
                ));
            return $resultSelect['url'];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function deleteNzb() {
        try {
            $sql = "delete from nzb;";
            $this->dbLocal->exec($sql);
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function insertNzb($url) {
        try {
            $sql = "INSERT INTO nzb (url) VALUES ('" . $url . "');";
            $this->dbLocal->exec($sql);
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function getLastNzbId() {
        try {
            $sql = "select * from nzb order by id desc ;";
            $resultSelect = $this->dbLocal->fetchAll($sql);
            return $resultSelect[0]['id'];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function findNzb($id) {
        $status = false;
        $msg = "";
        $data = array();
        try {
            if (! $this->deleteNzb()) {
                throw new \Exception("Erreur deleteNzb");
            }
            $binariesElement = $this->getBinById($id);
            if (! is_array($binariesElement)) {
                throw new \Exception($binariesElement);
            }
            $reader = new \PicoFeed\Reader\Reader();
            $fileNameClean = str_replace(" ", "%", $binariesElement['filename']);
            $nzbSearchs = $this->getAllNzb();
            foreach ($nzbSearchs as $nzbSearch) {
                try {
                    $rss = $nzbSearch['requestLink'] . $fileNameClean;
                    $beginTime = time();
                    $nbError = 0;
                    $findResource = false;
                    while (($nbError < 10) || (! $findResource)) {
                        try {
                            $resource = $reader->download($rss, '', '');
                            $parser = $reader->getParser($resource->getUrl(), 
                                $resource->getContent(), $resource->getEncoding());
                            if ($parser !== false) {
                                $findResource = true;
                            } else {
                                if ((time() - $beginTime) > 10) {
                                    $findResource = true;
                                }
                            }
                        } catch (\Exception $e) {
                            $nbError ++;
                        }
                    }
                    if ($findResource) {
                        if ($parser !== false) {
                            $feed = $parser->execute();
                            $items = $feed->items;
                            $countData = 0;
                            foreach ($items as $item) {
                                /**
                                 * *** urlNzb ****
                                 */
                                $urlTmp = str_replace('http://', '', $item->url);
                                $contentUrl = explode("/", $urlTmp);
                                $urlNzb = trim($contentUrl[2]);
                                
                                /**
                                 * *** timestamp ****
                                 */
                                $timestamp = date("d/m/Y H:i:s", $item->updated);
                                /**
                                 * *** size ****
                                 */
                                $content = explode("<br/>", $item->content);
                                $sizeSearch = explode(",", $nzbSearch['sizeSearch']);
                                $elementsReplace = explode(";", $sizeSearch[1]);
                                $sizeTmp = $content[$sizeSearch[0]];
                                foreach ($elementsReplace as $elementReplace) {
                                    $sizeTmp = str_replace($elementReplace, "", $sizeTmp);
                                }
                                $size = trim($sizeTmp);
                                // $pos = stripos($size, 'KB');
                                // if ( $pos === false ){
                                $title = str_replace(" ", "_", $binariesElement['title']);
                                $title = str_replace('"', "", $title);
                                $title = str_replace('\\', "", $title);
                                $title = str_replace('#', "", $title);
                                $urlNzb = $nzbSearch['retrieveLink'] . $urlNzb;
                                $shortTitle = $item->title;
                                $isShortTitle = false;
                                if (strlen($item->title) > 30) {
                                    $isShortTitle = true;
                                    $shortTitle = substr($item->title, 0, 30) . "...";
                                }
                                if ($this->insertNzb($urlNzb)) {
                                    $idNzb = $this->getLastNzbId();
                                    $data[] = array(
                                        "id" => $idNzb,
                                        "title" => $item->title,
                                        "isShortTitle" => $isShortTitle,
                                        "shortTitle" => $shortTitle,
                                        "titleToSave" => $title,
                                        "urlNzb" => $urlNzb,
                                        "timestamp" => $timestamp,
                                        "size" => $size
                                    );
                                }
                                $countData ++;
                                // }
                            }
                            if ($countData > 0) {
                                $status = true;
                                $msg = $countData . " liens trouv&eacute;s";
                                if ($countData == 1) {
                                    $msg = "Lancement du téléchargement";
                                }
                            } else {
                                $msg = "Impossible de trouver le nzb";
                            }
                        } else {
                            $msg = "Erreur d'url";
                        }
                        if ($status) {
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    // return array();
                }
            }
            return $data;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}