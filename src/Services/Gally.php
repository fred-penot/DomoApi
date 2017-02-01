<?php
namespace DomoApi\Services;

class Gally {
    private $db = null;
    private $log = null;

    public function __construct($db) {
        $this->db = $db;
    }

    public function __destruct() {}

    public function getVocalCommand() {
        try {
            $sql = "SELECT cv.id as commande_vocale_id, cv.name as command, cf.name as function, cv.need_response as need_response ".
                 " FROM commande_vocale cv JOIN commande_function cf ON cf.id=cv.commande_function_id ;";
            $resultCommand = $this->db->fetchAll($sql);
            $commands = [];
            foreach ($resultCommand as $command) {
                $sqlKeyword = "SELECT `key`, `value` FROM commande_vocale_keyword " .
                     "WHERE commande_vocale_id = ".$command['commande_vocale_id']." ;";
                $resultKeyword = $this->db->fetchAll($sqlKeyword);
                $sqlMessageSuccess = "SELECT gim.message, cvm.is_repeat FROM commande_vocale_message cvm " .
                    "JOIN gally_ia_message gim ON gim.id=cvm.gally_ia_message_id " .
                    "WHERE cvm.commande_vocale_id = ".$command['commande_vocale_id']." AND success=1 ;";
                $sqlMessageError = "SELECT gim.message, cvm.is_repeat FROM commande_vocale_message cvm " .
                    "JOIN gally_ia_message gim ON gim.id=cvm.gally_ia_message_id " .
                    "WHERE cvm.commande_vocale_id = ".$command['commande_vocale_id']." AND success=0 ;";
                $resultMessageSuccess = $this->db->fetchAll($sqlMessageSuccess);
                $resultMessageError = $this->db->fetchAll($sqlMessageError);
                $messages = [
                    'success' => $resultMessageSuccess,
                    'error' => $resultMessageError,
                ];
		        $commands[] = [
                    "id" => $command['commande_vocale_id'],
                    "command" => $command['command'],
                    "function" => $command['function'],
                    "need_response" => $command['need_response'],
                    "message" => $messages,
                    "keyword" => $resultKeyword,
                ];
            }
            return $commands;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function talkTo($name1, $function) {
        try {
            $acquaintance = $this->getAcquaintance($name1);
            if ($acquaintance instanceof \Exception) {
                throw new \Exception($acquaintance->getMessage());
            }
            $getCommandFunction = $this->getCommandFunction($function);
            if ($getCommandFunction instanceof \Exception) {
                throw new \Exception($getCommandFunction->getMessage());
            }
            $checkAcquaintancePresence = $this->checkAcquaintancePresence($acquaintance['id']);
            if ($checkAcquaintancePresence instanceof \Exception) {
                throw new \Exception($checkAcquaintancePresence->getMessage());
            }
            $getSentenceAcquaintanceByTypeAndFunction = $this->getSentenceAcquaintanceByTypeAndFunction(
                $acquaintance['acquaintance_type_id'], $getCommandFunction['id']);
            if ($getSentenceAcquaintanceByTypeAndFunction instanceof \Exception) {
                throw new \Exception($getSentenceAcquaintanceByTypeAndFunction->getMessage());
            }
            $sentence = str_replace('__NAME__', $acquaintance['name'], 
                $getSentenceAcquaintanceByTypeAndFunction[0]['sentence']);
            /*
             * if ( $checkAcquaintancePresence ) {
             * $sentence = "Je sais ! Je lui ai déjà dit bonjour !";
             * } else {
             * $getSentenceAcquaintanceByType = $this->getSentenceAcquaintanceByType(
             * $acquaintance['acquaintance_type_id']);
             * if ($getSentenceAcquaintanceByType instanceof \Exception) {
             * throw new \Exception($getSentenceAcquaintanceByType->getMessage());
             * }
             * $sentence = str_replace('__NAME__', $acquaintance['name'], $getSentenceAcquaintanceByType['sentence']);
             * }
             */
            return $sentence;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function setParam($userId, $sexe, $gallyName, $birthTimestamp, $birthNameCity, $birthCp, $currentNameCity, $currentCp) {
        try {
            $queryCheck = 'SELECT * FROM gally_ia_setting WHERE `user_id`='.$userId.' ;';
            $resultCheck = $this->db->fetchAll($queryCheck);
            if (count($resultCheck)==0) {
                $query = 'INSERT INTO gally_ia_setting (`user_id`, `sexe`, `name`, `birth_timestamp`, `birth_city`, `birth_cp`, `current_city`, `current_cp`) VALUES ('.$userId.', "' .
                    $sexe . '", "'.$gallyName.'", '.$birthTimestamp.', "'.$birthNameCity.'", "'.$birthCp.'", "'.$currentNameCity.'", "'.$currentCp.'");';
            } else {
                $query = 'UPDATE gally_ia_setting SET `sexe`="'.$sexe.'", `name`="'.$gallyName.'", `birth_timestamp`='.$birthTimestamp.', `birth_city`="'.$birthNameCity.'", `birth_cp`="'.$birthCp.'", `current_city`="'.$currentNameCity.'", `current_cp`="'.$currentCp.'" WHERE `user_id`='.$userId.' ;';
            }
            $result = $this->db->exec($query);
            if (! $result) {
                throw new \Exception(
                    "Erreur lors de la sauvegarde des paramètres.");
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getParam($userId) {
        try {
            $param = [];
            $query = 'SELECT `sexe`, `name`, `birth_timestamp`, `birth_city`, `birth_cp`, `current_city`, `current_cp` FROM gally_ia_setting WHERE `user_id`='.$userId.' ;';
            $result = $this->db->fetchAll($query);
            if (count($result)>0) {
                $param = $result[0];
            }
            return $param;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function setHistory($userId, $commandeVocaleId, $timestamp) {
        try {
            $query = 'INSERT INTO gally_ia_history (`user_id`, `commande_vocale_id`, `timestamp`) VALUES ('.$userId.', ' .
                $commandeVocaleId.', '.$timestamp.');';
            $result = $this->db->exec($query);
            if (! $result) {
                throw new \Exception(
                    "Erreur lors de la sauvegarde de l'historique.");
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getHistoryCommandDay($userId, $commandeVocaleId, $timestamp) {
        try {
            $timestampToDate = new \DateTime();
            $timestampToDate->setTimestamp($timestamp);
            $timestamp1 = new \DateTime($timestampToDate->format('Y-m-d').'T00:00:00+0100',
                new \DateTimeZone("Europe/Paris"));
            $timestamp2 = new \DateTime($timestampToDate->format('Y-m-d').'T23:59:59+0100',
                new \DateTimeZone("Europe/Paris"));
            $query = 'SELECT * FROM gally_ia_history WHERE `user_id`='.$userId.
                ' AND `commande_vocale_id`='.$commandeVocaleId.
                ' AND `timestamp` BETWEEN '.$timestamp1->getTimestamp().
                ' AND '.$timestamp2->getTimestamp().';';
            $result = $this->db->fetchAll($query);
            return count($result);
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getIaMessages() {
        try {
            $query = 'SELECT * FROM gally_ia_message ;';
            $result = $this->db->fetchAll($query);
            return $result;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getIaMessagesByName($word) {
        try {
            $query = 'SELECT * FROM gally_ia_message WHERE message like "%'.$word.'%" ;';
            $result = $this->db->fetchAll($query);
            return $result;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getCommandesVocale() {
        try {
            $query = 'SELECT cv.id as id, cv.name as command_name, cf.name as function_name FROM commande_vocale cv '.
                'JOIN commande_function cf ON cf.id=cv.commande_function_id;';
            $result = $this->db->fetchAll($query);
            return $result;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getCommandesVocaleByName($word) {
        try {
            $query = 'SELECT cv.id as id, cv.name as command_name, cf.name as function_name FROM commande_vocale cv '.
                'JOIN commande_function cf ON cf.id=cv.commande_function_id '.
                'WHERE cv.name like "%'.$word.'%" ;';
            $result = $this->db->fetchAll($query);
            return $result;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function saveCommandeVocaleMessage($commandId, $messageId, $success, $repeat) {
        try {
            $query = 'INSERT INTO commande_vocale_message (`commande_vocale_id`, `gally_ia_message_id` , `success`, `is_repeat`) VALUES ' .
                '('.$commandId.', '.$messageId.', '.$success.', '.$repeat.');';
            $result = $this->db->exec($query);
            if (! $result) {
                throw new \Exception(
                    "Erreur lors de la sauvegarde du message de la commande vocale.");
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getIaScenarii() {
        try {
            $query = 'SELECT gis.id as id, gis.name as name, gis.gally_ia_type_scenario_id as type_id, '.
                'gits.name as type_name FROM gally_ia_scenario gis '.
                'JOIN gally_ia_type_scenario gits ON gits.id=gis.gally_ia_type_scenario_id;';
            $result = $this->db->fetchAll($query);
            return $result;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getIaScenariiByName($word) {
        try {
            $query = 'SELECT gis.id as id, gis.name as name, gis.gally_ia_type_scenario_id as type_id, '.
                'gits.name as type_name FROM gally_ia_scenario gis '.
                'JOIN gally_ia_type_scenario gits ON gits.id=gis.gally_ia_type_scenario_id '.
                'WHERE gis.name like "%'.$word.'%" ;';
            $result = $this->db->fetchAll($query);
            return $result;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getIaScenarioByTypeAndName($typeId, $word) {
        try {
            $query = 'SELECT gis.id, gis.name as name, gis.gally_ia_type_scenario_id as type_id '.
                'FROM gally_ia_scenario gis '.
                'WHERE name like "%'.$word.'%" AND gally_ia_type_scenario_id='.$typeId.' ;';
            $result = $this->db->fetchAll($query);
            return $result[0];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function launchIaScenario($id) {
        try {
            $query = 'SELECT gise.value as value, gits.value as type '.
                'FROM gally_ia_scenario_element gise '.
                'JOIN gally_ia_scenario gis ON gis.id=gise.gally_ia_scenario_id '.
                'JOIN gally_ia_type_scenario gits ON gits.id=gis.gally_ia_type_scenario_id '.
                'WHERE gis.id='.$id.' ;';
            $elements = $this->db->fetchAll($query);
            foreach ($elements as $element) {
                if ($element['type'] == 'light') {
                    $this->putLightAction($element['value'], true);
                } else if ($element['type'] == 'audio') {
                    // todo
                }
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function putLightAction($name, $action) {
        try {
            $arg = "f";
            if (true) {
                $arg = "n";
            }
            $output = array();
            exec('tdtool -' . $arg . ' ' . $name, $output);
            $statut = false;
            if (count($output) > 0) {
                if (substr($output[0], - 7, 7) == 'Success') {
                    $statut = true;
                }
            }
            return $statut;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function getCommandFunction($name) {
        try {
            $sqlCommandFunction = "SELECT * FROM commande_function WHERE UPPER(name)='" .
                 strtoupper($name) . "' ;";
            $resultCommandFunction = $this->db->fetchAssoc($sqlCommandFunction);
            if (! $resultCommandFunction) {
                throw new \Exception("Pas de correspondance pour la fonction " . $name . " !");
            }
            return $resultCommandFunction;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function getAcquaintance($name) {
        try {
            $sqlKw = "SELECT * FROM acquaintance_kw WHERE UPPER(name)='" . strtoupper($name) . "' ;";
            $resultKw = $this->db->fetchAll($sqlKw);
            if (! $resultKw) {
                throw new \Exception("Pas de correspondance pour la personne " . $name . " !");
            }
            $sql = "SELECT * FROM acquaintance WHERE id=? ;";
            $result = $this->db->fetchAssoc($sql, 
                array(
                    (int) $resultKw[0]['acquaintance_id']
                ));
            return $result;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function checkAcquaintancePresence($acquaintanceId) {
        try {
            $today = \DateTime::createFromFormat('d/m/Y', date('d/m/Y'));
            $today->setTime(0, 0, 0);
            $timestamp1 = $today->getTimestamp();
            $today->setTime(23, 59, 59);
            $timestamp2 = $today->getTimestamp();
            $sqlPresence = "SELECT * FROM acquaintance_presence_day WHERE acquaintance_id=" .
                 $acquaintanceId . " AND timestamp between " . $timestamp1 . " AND " . $timestamp2 .
                 " ;";
            $resultPresence = $this->db->fetchAll($sqlPresence);
            if (count($resultPresence) > 0) {
                return true;
            } else {
                $sqlInsertPresence = "INSERT INTO acquaintance_presence_day (acquaintance_id, timestamp) VALUES (" .
                     $acquaintanceId . ", " . time() . ") ;";
                $resultPresence = $this->db->exec($sqlInsertPresence);
                return false;
            }
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function getSentenceAcquaintanceByTypeAndFunction($typeId, $functionId) {
        try {
            $sql = "SELECT * FROM acquaintance_type_sentence WHERE acquaintance_type_id=" . $typeId .
                 " ORDER BY id;";
            $result = $this->db->fetchAll($sql);
            if (count($result) == 0) {
                throw new \Exception("Pas de correspondance pour le type demandé  !");
            }
            $acquaintanceSentenceIds = array();
            foreach ($result as $acquaintanceTypeSentence) {
                $acquaintanceSentenceIds[] = $acquaintanceTypeSentence['acquaintance_sentence_id'];
            }
            $ids = implode(',', $acquaintanceSentenceIds);
            $sqlSentence = "SELECT * FROM acquaintance_sentence WHERE id IN (" . $ids .
                 ") AND commande_function_id=" . $functionId . " ;";
            $resultSentence = $this->db->fetchAll($sqlSentence);
            return $resultSentence;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}
