<?php
namespace DomoApi\Services;

class Forex {
    private $db = null;

    public function __construct($db) {
        $this->db = $db;
    }

    public function __destruct() {}

    public function getTables() {
        try {
            $sql = "SHOW TABLES;";
            $results = $this->db->fetchAll($sql);
            $tables = array();
            foreach ($results as $result) {
                $tables[] = $result['Tables_in_Forex'];
            }
            return $tables;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getById($table, $id) {
        try {
            $sql = "SELECT id, timestamp, millisec, currency1 as base, currency2 as `contre-partie` FROM " .
                 $table . " WHERE id=? ;";
            $result = $this->db->fetchAssoc($sql, 
                array(
                    (int) $id
                ));
            return $result;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getByTimestamp($table, $timestamp) {
        try {
            $sql = "SELECT id, timestamp, millisec, currency1 as base, currency2 as `contre-partie` FROM " .
                 $table . " WHERE timestamp=" . $timestamp . " ORDER BY millisec ASC;";
            $result = $this->db->fetchAll($sql);
            return $result;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getByPeriode($table, $timestamp1, $timestamp2) {
        try {
            $sql = "SELECT id, timestamp, millisec, currency1 as base, currency2 as `contre-partie` FROM " .
                 $table . " WHERE timestamp BETWEEN " . $timestamp1 . " AND " . $timestamp2 .
                 " ORDER BY timestamp, millisec ASC;";
            $result = $this->db->fetchAll($sql);
            return $result;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function insertCurrencyArchive($table, $file) {
        try {
            $checkTable = $this->checkTableArchive($table);
            if ($checkTable instanceof \Exception) {
                throw new \Exception($checkTable->getMessage());
            }
            if (($handle = fopen($file, "r")) !== FALSE) {
                $insertValues = array();
                while (($row = fgetcsv($handle, 60, ",")) !== FALSE) {
                    list ($type, $dateFormated, $currency1, $currency2) = $row;
                    list ($dateString, $hourFull) = explode(' ', $dateFormated);
                    list ($hour, $milliSec) = explode('.', $hourFull);
                    $year = substr($dateString, 0, 4);
                    $month = substr($dateString, 4, 2);
                    $day = substr($dateString, 6, 2);
                    $dateTime = new \DateTime($year . "-" . $month . "-" . $day . "T" . $hour);
                    $dateTime->setTimeZone(new \DateTimeZone("Europe/Paris"));
                    $insertValues[] = '(' . $dateTime->getTimestamp() . ', ' . $milliSec . ', ' .
                         $currency1 . ', ' . $currency2 . ')';
                    if (count($insertValues) > 1000) {
                        $values = implode(',', $insertValues);
                        $queryInsert = 'INSERT INTO ' . $table .
                             ' (timestamp, millisec, currency1, currency2) VALUES ' . $values . ' ;';
                        $result = $this->db->exec($queryInsert);
                        $insertValues = array();
                    }
                    gc_collect_cycles();
                }
                if (count($insertValues) > 0) {
                    $values = implode(',', $insertValues);
                    $queryInsert = 'INSERT INTO ' . $table .
                         ' (timestamp, millisec, currency1, currency2) VALUES ' . $values . ' ;';
                    $result = $this->db->exec($queryInsert);
                }
                fclose($handle);
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getAuthId($url, $login, $password) {
        try {
            $urlAuth = str_replace(
                array(
                    '__LOGIN__',
                    '__PASSWORD__'
                ), 
                array(
                    $login,
                    $password
                ), $url);
            return trim(file_get_contents($urlAuth));
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getCurrentCotations($url, $idAuth) {
        try {
            $urlCotations = str_replace('__ID__', $idAuth, $url);
            $cotations = explode("\n", trim(file_get_contents($urlCotations)));
            $currentCotations = array();
            foreach ($cotations as $cotation) {
                list ($devise, $timestampMs, $bidBigFigure, $bidPoints, $offerBigFigure, $offerPoints, $high, $low, $open) = explode(
                    ',', $cotation);
                $currentCotations[] = array(
                    "cotation" => $devise,
                    "timestampMs" => $timestampMs,
                    "bidBigFigure" => $bidBigFigure,
                    "bidPoints" => $bidPoints,
                    "offerBigFigure" => $offerBigFigure,
                    "offerPoints" => $offerPoints,
                    "high" => $high,
                    "low" => $low,
                    "open" => $open
                );
            }
            return $currentCotations;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function insertCurrentCotations($url, $idAuth) {
        try {
            $urlCotations = str_replace('__ID__', $idAuth, $url);
            $cotations = explode("\n", trim(file_get_contents($urlCotations)));
            if (count($cotations) < 4) {
                throw new \Exception("Marché fermé : " . $urlCotations);
            }
            foreach ($cotations as $cotation) {
                list ($devise, $timestampMs, $bidBigFigure, $bidPoints, $offerBigFigure, $offerPoints, $high, $low, $open) = explode(
                    ',', $cotation);
                $table = trim(str_replace('/', '', $devise));
                $checkTable = $this->checkTableCurrent($table);
                if ($checkTable instanceof \Exception) {
                    throw new \Exception($checkTable->getMessage());
                }
                $sql = 'SELECT * FROM ' . $table . '_CURRENT WHERE timestamp_ms=' . $timestampMs .
                     ';';
                $result = $this->db->fetchAll($sql);
                if (count($result) == 0) {
                    $values = '(' . $timestampMs . ',' . $bidBigFigure . ',' . $bidPoints . ',' .
                         $offerBigFigure . ',' . $offerPoints . ',' . $high . ',' . $low . ',' .
                         $open . ')';
                    $queryInsert = 'INSERT INTO ' . $table . '_CURRENT' .
                         ' (timestamp_ms, bid_big_figure, bid_points, offer_big_figure, offer_points, high, low, open) VALUES ' .
                         $values . ' ;';
                    $this->db->exec($queryInsert);
                }
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function checkTableArchive($table) {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS `" . $table . "` (" .
                 "`id` INT NOT NULL AUTO_INCREMENT, `timestamp` BIGINT NOT NULL, " .
                 "`millisec` INT NULL, `currency1` FLOAT NOT NULL, " .
                 "`currency2` FLOAT NOT NULL, PRIMARY KEY (`id`));";
            $result = $this->db->exec($sql);
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function checkTableCurrent($table) {
        try {
            $sql = "SHOW TABLES;";
            $results = $this->db->fetchAll($sql);
            $find = false;
            foreach ($results as $result) {
                if ($result['Tables_in_Forex'] == $table . "_CURRENT") {
                    $find = true;
                }
            }
            if (! $find) {
                $sql = "CREATE TABLE IF NOT EXISTS `" . $table . "_CURRENT` (" .
                     "`id` INT NOT NULL AUTO_INCREMENT, `timestamp_ms` BIGINT NOT NULL," .
                     "`bid_big_figure` FLOAT NOT NULL, `bid_points` INT NOT NULL," .
                     "`offer_big_figure` FLOAT NOT NULL, `offer_points` INT NOT NULL," .
                     "`high` FLOAT NOT NULL, `low` FLOAT NOT NULL, `open` FLOAT NOT NULL," .
                     "PRIMARY KEY (`id`));";
                $result = $this->db->exec($sql);
                $sql = "ALTER TABLE `" . $table . "_CURRENT` ADD INDEX(`timestamp_ms`);";
                $this->db->exec($sql);
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}