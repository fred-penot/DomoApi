<?php
namespace DomoApi\Services;

class Sabnzbd
{

    public function __construct()
    {}

    public function __destruct()
    {}

    public function getAllDownload($urlSabApi)
    {
        try {
            $downloads = array();
            $infos = json_decode(file_get_contents($urlSabApi));
            if (! isset($infos->error)) {
                if (isset($infos->queue)) {
                    if (isset($infos->queue->slots)) {
                        foreach ($infos->queue->slots as $slot) {
                            $isShortTitle = false;
                            $shortTitle = $slot->filename;
                            $title = $slot->filename;
                            if (strlen($title) > 10) {
                                $isShortTitle = true;
                                $shortTitle = substr($title, 0, 10) . "...";
                            }
                            $isShortCategory = false;
                            $priority = $slot->priority;
                            $category = $slot->cat;
                            $shortCategory = $slot->cat;
                            if (strlen($category) > 10) {
                                $isShortCategory = true;
                                $shortCategory = substr($category, 0, 10) . "...";
                            }
                            if ($slot->status == "Queued" || $slot->status == "Paused") {
                                $icon = "ion-play";
                                $isPause = 1;
                                $next = 0;
                                $txt = "Reprendre";
                            } else {
                                $icon = "ion-pause";
                                $isPause = 0;
                                $next = 1;
                                $txt = "Pause";
                            }
                            $pause = array(
                                "icon" => $icon,
                                "next" => $next,
                                "txt" => $txt
                            );
                            $downloads[] = array(
                                "id" => $slot->nzo_id,
                                "isShortTitle" => $isShortTitle,
                                "shortTitle" => $shortTitle,
                                "title" => $title,
                                "isShortCategory" => $isShortCategory,
                                "shortCategory" => $shortCategory,
                                "category" => $category,
                                "priority" => $priority,
                                "timeleft" => $slot->timeleft,
                                "sizeMo" => $slot->mb,
                                "sizeLeftMo" => $slot->mbleft,
                                "isPause" => $isPause,
                                "pause" => $pause
                            );
                        }
                    }
                }
            }
            return $downloads;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getInfoServer($urlSabApi)
    {
        try {
            $statut = false;
            $server = array();
            $infos = json_decode(file_get_contents($urlSabApi));
            if (! isset($infos->error)) {
                if (isset($infos->queue)) {
                    if ($infos->queue->paused) {
                        $pauseInfo = array(
                            "statut" => 1,
                            "icon" => "ion-play",
                            "txt" => "Reprendre",
                            "next" => 0
                        );
                    } else {
                        $pauseInfo = array(
                            "statut" => 0,
                            "icon" => "ion-pause",
                            "txt" => "Mettre en pause",
                            "next" => 1
                        );
                    }
                    $server["pause"] = $pauseInfo;
                    $server["speed"] = $infos->queue->speed;
                    $server["speedLimit"] = $infos->queue->speedlimit;
                    $server["diskspaceDownload"] = $infos->queue->diskspace1;
                    $server["diskspaceFinish"] = $infos->queue->diskspace2;
                    $server["sizeleft"] = str_replace("B", "o", $infos->queue->sizeleft);
                    $server["timeleft"] = $infos->queue->timeleft;
                    $server["countSlot"] = count($infos->queue->slots);
                    $statut = true;
                }
            }
            return $server;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function pause($urlSabApi, $isPause, $commandPause, $commandResume, $id, $commandPauseId, $commandResumeId)
    {
        try {
            if (trim($isPause) == "1") {
                if ($id == "0") {
                    $urlSabApi .= $commandPause;
                } else {
                    $urlSabApi .= $commandPauseId . $id;
                }
                $icon = "ion-play";
                $txt = "Reprendre";
                $next = 0;
            } else {
                if ($id == "0") {
                    $urlSabApi .= $commandResume;
                } else {
                    $urlSabApi .= $commandResumeId . $id;
                }
                $icon = "ion-pause";
                $txt = "Mettre en pause";
                $next = 1;
            }
            $statut = true;
            $infos = json_decode(file_get_contents($urlSabApi));
            if (! isset($infos->error)) {
                if (! (isset($infos->status) && $infos->status)) {
                    $statut = false;
                    $icon = "fa-ban";
                    $txt = "";
                }
            }
            return array(
                "statut" => $statut,
                "data" => array(
                    "icon" => $icon,
                    "txt" => $txt,
                    "next" => $next
                )
            );
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function setSpeed($urlSabApi)
    {
        try {
            $statut = true;
            $infos = json_decode(file_get_contents($urlSabApi));
            if (! isset($infos->error)) {
                if (! (isset($infos->status) && $infos->status)) {
                    $statut = false;
                }
            }
            return $statut;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function delete($urlSabApi)
    {
        try {
            $statut = false;
            $info = json_decode(file_get_contents($urlSabApi));
            if (! isset($info->error)) {
                $statut = true;
            }
            return $statut;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getCategories($urlSabApi)
    {
        try {
            $statut = false;
            $categories = array();
            $infos = json_decode(file_get_contents($urlSabApi));
            if (! isset($infos->error)) {
                if (isset($infos->categories)) {
                    foreach ($infos->categories as $category) {
                        if ($category == '*') {
                            $category = "aucune";
                        }
                        $categories[] = $category;
                    }
                    $statut = true;
                }
            }
            if (! $statut) {
                throw new \Exception("Erreur lors de la récupération des catégories.");
            }
            return $categories;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function change($urlSabApi, $id, $name)
    {
        try {
            $urlSabApiChange = str_replace(array(
                ":id",
                ":name"
            ), array(
                $id,
                str_replace(" ", "%20", $name)
            ), $urlSabApi);
            $statut = false;
            $infos = json_decode(file_get_contents($urlSabApiChange));
            if (! isset($infos->error)) {
                if ((isset($infos->status) && $infos->status)) {
                    $statut = true;
                }
            }
            if (! $statut) {
                throw new \Exception("Erreur lors du changement.");
            }
            return $statut;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getAllHistory($urlSabApi)
    {
        try {
            $histories = array();
            $info = json_decode(file_get_contents($urlSabApi));
            if (! isset($info->error)) {
                if (isset($info->history)) {
                    $slots = $info->history->slots;
                    foreach ($slots as $slot) {
                        $title = $slot->name;
                        $shortTitle = $slot->name;
                        $isShortTitle = false;
                        if (strlen($title) > 12) {
                            $isShortTitle = true;
                            $shortTitle = substr($title, 0, 12) . "...";
                        }
                        if ($slot->status == "Completed") {
                            $infoStatus = array(
                                "icon" => "smile-o",
                                "class" => "text-success",
                                "word" => "succès",
                                "statut" => true
                            );
                        } elseif ($slot->status == "Failed") {
                            $infoStatus = array(
                                "icon" => "frown-o",
                                "class" => "text-danger",
                                "word" => "erreur",
                                "statut" => false
                            );
                        } else {
                            $infoStatus = array(
                                "icon" => "meh-o",
                                "class" => "text-warning",
                                "word" => "attention",
                                "statut" => false
                            );
                        }
                        $histories[] = array(
                            "id" => $slot->nzo_id,
                            "pathTemp" => $slot->path,
                            "pathEnd" => $slot->storage,
                            "date" => date("d/m/Y H:i:s", $slot->completed),
                            "title" => $title,
                            "shortTitle" => $shortTitle,
                            "isShortTitle" => $isShortTitle,
                            "info" => $infoStatus
                        );
                    }
                }
            }
            return $histories;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getLog($urlSabApi, $id)
    {
        try {
            $title = "";
            $log = array();
            $statut = false;
            $infoSabNzbd = json_decode(file_get_contents($urlSabApi));
            if (! isset($infoSabNzbd->error)) {
                if (isset($infoSabNzbd->history)) {
                    $slots = $infoSabNzbd->history->slots;
                    foreach ($slots as $slot) {
                        if ($slot->nzo_id == $id) {
                            $title = $slot->name;
                            $logSabNzbds = $slot->stage_log;
                            foreach ($logSabNzbds as $logSabNzbd) {
                                $actions = $logSabNzbd->actions;
                                $type = $logSabNzbd->name;
                                if ($type != "Source") {
                                    foreach ($actions as $key => $action) {
                                        $log[] = $action;
                                    }
                                }
                            }
                            $statut = true;
                            break;
                        }
                    }
                }
            }
            if (! $statut) {
                throw new \Exception("Erreur lors de la récupération de la log.");
            }
            return array(
                "title" => $title,
                "log" => $log
            );
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function sendUrlNzb($urlSabApi, $urlNzb, $title)
    {
        try {
            $urlSabApiSend = str_replace(array(
                ":url",
                ":title"
            ), array(
                $urlNzb,
                str_replace("_", "%20", $title)
            ), $urlSabApi);
            $info = json_decode(file_get_contents($urlSabApiSend));
            if (! isset($info->error)) {
                return true;
            } else {
                throw new \Exception("Erreur lors du lancement du téléchargement.");
            }
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}