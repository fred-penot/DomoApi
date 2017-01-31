<?php
namespace DomoApi\Services;

class Security {
    private $db = null;

    public function __construct($db) {
        $this->db = $db;
    }

    public function __destruct() {}

    public function auth($login, $password) {
        try {
            $query = "SELECT * FROM user where login='" . $login . "' and password='" .
                 md5($password) . "';";
            $result = $this->db->fetchAll($query);
            if (count($result) == 0) {
                throw new \Exception("Utilisateur inconnu");
            }
            $token = $this->generateAndUpdateToken($result[0]["id"]);
            $profil = $result[0]["profil"];
            if ($token instanceof \Exception) {
                throw new \Exception($token->getMessage());
            }
            return [
                'token' => $token,
                'profil' => $profil,
            ];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function generateAndUpdateToken($userId) {
        try {
            $token = bin2hex(openssl_random_pseudo_bytes(16));
            $queryDelete = "DELETE FROM token_user WHERE  user_id='" . $userId . "';";
            $result = $this->db->exec($queryDelete);
            $queryInsert = "INSERT INTO token_user (user_id, value, time) VALUES ('" . $userId .
                 "', '" . $token . "', '" . time() . "');";
            $result = $this->db->exec($queryInsert);
            if (! $result) {
                throw new \Exception("Erreur lors de l'injection du token de l'utilisateur.");
            }
            return $token;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function checkAndUpdateToken($token) {
        try {
            $oldTime = time() - (60 * 15);
            $queryDelete = "DELETE FROM token_user WHERE time<'" . $oldTime . "';";
            $resultDelete = $this->db->exec($queryDelete);
            $querySelect = "SELECT * FROM token_user WHERE value='" . $token . "';";
            $resultSelect = $this->db->fetchAll($querySelect);
            if (count($resultSelect) == 1) {
                $userId = $resultSelect[0]["user_id"];
                $querySelect = "SELECT * FROM token_user WHERE user_id='" . $userId .
                     "' ORDER BY id ASC;";
                $resultSelect = $this->db->fetchAll($querySelect);
                $countResultSelect = count($resultSelect);
                if (count($resultSelect) > 9) {
                    $queryDelete = "DELETE FROM token_user WHERE id='" . $resultSelect[0]["id"] .
                         "';";
                    $resultDelete = $this->db->exec($queryDelete);
                }
                $token = bin2hex(openssl_random_pseudo_bytes(16));
                $queryInsert = "INSERT INTO token_user (user_id, value, time) VALUES ('" . $userId .
                     "', '" . $token . "', '" . time() . "');";
                $result = $this->db->exec($queryInsert);
                if (! $result) {
                    throw new \Exception("Erreur lors de l'injection du token de l'utilisateur.");
                }
            } else {
                throw new \Exception("Erreur lors du contrôle d'autentification.");
            }
            return $token;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
    
    public function getUser($token) {
        try {
            $querySelect = "SELECT * FROM token_user WHERE value='" . $token . "';";
            $resultSelect = $this->db->fetchAll($querySelect);
            if (count($resultSelect) == 1) {
                $userId = $resultSelect[0]['user_id'];
            } else {
                throw new \Exception("Erreur lors du contrôle d'autentification.");
            }
            return $userId;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}