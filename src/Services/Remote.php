<?php
namespace DomoApi\Services;

class Remote {
    private $db = null;
    private $log = null;

    public function __construct($db) {
        $this->db = $db;
    }

    public function __destruct() {}

    public function saveAction($userId, $action) {
        try {
            $query = 'INSERT INTO gally_client_action (`user_id`, `action`, `timestamp`) VALUES ('.$userId.', "' .
                $action . '", '.time().');';
            $result = $this->db->exec($query);
            if (! $result) {
                throw new \Exception(
                    "Erreur lors de la sauvegarde de l'action.");
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getAction($userId) {
        try {
            $action = '';
            /*$query = 'SELECT `id`, `action` FROM gally_client_action WHERE `user_id`='.$userId.' AND done=0 '.
            'ORDER by id ASC LIMIT 1;';*/
            $query = 'SELECT `id`, `action` FROM gally_client_action WHERE done=0 '.
                'ORDER by id ASC LIMIT 1;';
            $result = $this->db->fetchAll($query);
            if (count($result) > 0) {
                $action = $result[0]['action'];
                $queryUpdate = 'UPDATE gally_client_action SET done=1 WHERE id='.$result[0]['id'].' ;';
                $resultUpdate = $this->db->exec($queryUpdate);
                if (! $resultUpdate) {
                    throw new \Exception(
                        "Erreur lors de la mise à jour de l'action.");
                }
            }
            return $action;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}
