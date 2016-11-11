<?php
namespace DomoApi\Services;

class Freebox {
    private $url;
    private $box;
    private $id;
    private $token = null;
    private $sessionToken = null;

    public function __construct() {
        $this->url = "http://mafreebox.free.fr";
        $this->id = "fr.freebox.domoapi";
        $this->token = "";
        $this->sessionToken = null;
        $this->version();
    }

    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    public function authorize($name, $version, $device) {
        try {
            $call = $this->call("login/authorize", 
                array(
                    'app_id' => $this->id,
                    'app_name' => $name,
                    'app_version' => $version,
                    'device_name' => $device
                ));
            if ($call instanceof \Exception) {
                throw new \Exception($call->getMessage());
            }
            if (! $call->success) {
                throw new \Exception("Erreur d'autorisation : " . $call->msg);
            }
            $this->token = $call->result->app_token;
            return $call;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function trackAuthorize($trackId) {
        try {
            $callTracking = $this->call('login/authorize/' . $trackId);
            if ($callTracking instanceof \Exception) {
                throw new \Exception($callTracking->getMessage());
            }
            var_dump($callTracking);
            die();
            if ($callTracking->success) {
                var_dump($callTracking);
            } else {
                echo "erreur tracking";
            }
            die();
            // return $list;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function fileList() {
        try {
            $list = array();
            $login = $this->login();
            if ($login instanceof \Exception) {
                throw new \Exception($login->getMessage());
            }
            $path = base64_encode("Disque dur/Photos");
            $callList = $this->call('fs/ls/' . $path);
            if ($callList instanceof \Exception) {
                throw new \Exception($callList->getMessage());
            }
            if (! $callList->success) {
                throw new \Exception("Erreur liste : " . $callList->msg);
            }
            foreach ($callList->result as $file) {
                if (! $file->hidden) {
                    $list[] = $file->name;
                }
            }
            return $list;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getAirmediaReceivers() {
        try {
            $list = array();
            $login = $this->login();
            if ($login instanceof \Exception) {
                throw new \Exception($login->getMessage());
            }
            $call = $this->call('airmedia/receivers/');
            if ($call instanceof \Exception) {
                throw new \Exception($call->getMessage());
            }
            if (! $call->success) {
                throw new \Exception("Erreur airmedia receivers : " . $call->msg);
            }
            foreach ($call->result as $device) {
                if ($device->capabilities->video && $device->capabilities->photo) {
                    $list[] = array(
                        "name" => $device->name,
                        "password_protected" => $device->password_protected
                    );
                }
            }
            return $list;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function playMedia($type, $url, $device, $position = 0) {
        try {
            $login = $this->login();
            if ($login instanceof \Exception) {
                throw new \Exception($login->getMessage());
            }
            if ($type == 'photo') {
                $media = base64_encode($url);
            } else {
                $type = 'video';
                $media = $url;
            }
            $parameters = array(
                'action' => "start",
                'media_type' => $type,
                'media' => $media
            );
            if ($position > 0) {
                $parameters['position'] = $position;
            }
            $call = $this->call('airmedia/receivers/' . $device . '/', $parameters);
            if ($call instanceof \Exception) {
                throw new \Exception($call->getMessage());
            }
            if (! $call->success) {
                throw new \Exception("Erreur play media : " . $call->msg);
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function stopMedia($device) {
        try {
            $login = $this->login();
            if ($login instanceof \Exception) {
                throw new \Exception($login->getMessage());
            }
            $call = $this->call('airmedia/receivers/' . $device . '/', 
                array(
                    'action' => "stop",
                    'media_type' => "video"
                ));
            if ($call instanceof \Exception) {
                throw new \Exception($call->getMessage());
            }
            if (! $call->success) {
                throw new \Exception("Erreur stop media : " . $call->msg);
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getAllTasks() {
        try {
            $login = $this->login();
            if ($login instanceof \Exception) {
                throw new \Exception($login->getMessage());
            }
            $call = $this->call('fs/tasks/', array());
            if ($call instanceof \Exception) {
                throw new \Exception($call->getMessage());
            }
            if (! $call->success) {
                throw new \Exception("Erreur every tasks : " . $call->msg);
            }
            return $call->result;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getAllSharingLink() {
        try {
            $login = $this->login();
            if ($login instanceof \Exception) {
                throw new \Exception($login->getMessage());
            }
            $call = $this->call('share_link/', array());
            if ($call instanceof \Exception) {
                throw new \Exception($call->getMessage());
            }
            if (! $call->success) {
                throw new \Exception("Erreur all sharing link : " . $call->msg);
            }
            return $call->result;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function setSharingLink($url, $expire = 0) {
        try {
            $login = $this->login();
            if ($login instanceof \Exception) {
                throw new \Exception($login->getMessage());
            }
            $path = base64_encode($url);
            $call = $this->call('share_link/', 
                array(
                    'path' => $path,
                    'expire' => $expire,
                    'fullurl' => ""
                ), 'POST');
            if ($call instanceof \Exception) {
                throw new \Exception($call->getMessage());
            }
            if (! $call->success) {
                throw new \Exception("Erreur set sharing link : " . $call->msg);
            }
            return $call->result;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
    
    public function deleteSharingLink($token) {
        try {
            $login = $this->login();
            if ($login instanceof \Exception) {
                throw new \Exception($login->getMessage());
            }
            $call = $this->call('share_link/'.$token, array(), 'DELETE');
            if ($call instanceof \Exception) {
                throw new \Exception($call->getMessage());
            }
            if (! $call->success) {
                throw new \Exception("Erreur delete sharing link : " . $call->msg);
            }
            return $call->result;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function login() {
        try {
            $callLogin = $this->call("login");
            if ($callLogin instanceof \Exception) {
                throw new \Exception($callLogin->getMessage());
            }
            if (! $callLogin->success) {
                throw new \Exception("Erreur login : " . $callLogin->msg);
            }
            $challenge = $callLogin->result->challenge;
            $password = hash_hmac('sha1', $challenge, $this->token);
            $callSession = $this->call("login/session", 
                array(
                    'app_id' => $this->id,
                    'password' => $password
                ));
            if ($callSession instanceof \Exception) {
                throw new \Exception($callSession->getMessage());
            }
            if (! $callSession->success) {
                throw new \Exception("Erreur session : " . $callSession->msg);
            }
            $this->sessionToken = $callSession->result->session_token;
            return $callSession;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function version() {
        try {
            $path = "api_version";
            $content = file_get_contents("$this->url/$path");
            return $this->box = json_decode($content);
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function call($api_url, $params = array(), $method = null) {
        try {
            if (! $method) {
                $method = (! $params) ? 'GET' : 'POST';
            }
            $rurl = $this->url . $this->box->api_base_url . 'v' . intval($this->box->api_version) .
                 '/' . $api_url;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $rurl);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            if ($method == "POST") {
                curl_setopt($ch, CURLOPT_POST, true);
            } elseif ($method == "DELETE") {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            } elseif ($method == "PUT") {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            }
            if ($params) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            }
            if ($this->sessionToken) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, 
                    array(
                        "X-Fbx-App-Auth: $this->sessionToken"
                    ));
            }
            $content = curl_exec($ch);
            curl_close($ch);
            $r = json_decode($content);
            return $r;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}