<?php
namespace DomoApi\Services;

class Domotic {

    public function __construct() {}

    public function __destruct() {}

    public function getDevices() {
        try {
            $output = array();
            exec('tdtool --list-devices', $output);
            $listeDevice = array();
            foreach ($output as $line) {
                $lineDevice = array();
                $lineInfo = explode("\t", $line);
                foreach ($lineInfo as $info) {
                    $infoElement = explode("=", $info);
                    $lineDevice[$infoElement[0]] = strtolower($infoElement[1]);
                    if ($infoElement[0] == "name") {
                        if ($infoElement[1] == "salon-lampe") {
                            $lineDevice["label"] = "Salon";
                        }
                        if ($infoElement[1] == "salle_à_manger-halogène") {
                            $lineDevice["label"] = "Salle à manger";
                        }
                    }
                    if ($infoElement[0] == "lastsentcommand") {
                        if ($infoElement[1] == "OFF") {
                            $lineDevice["icon"] = "fa-toggle-off";
                            $lineDevice["putOn"] = true;
                        } else {
                            $lineDevice["icon"] = "fa-toggle-on";
                            $lineDevice["putOn"] = false;
                        }
                    }
                    $lineDevice["iconSize"] = "fa-5x";
                }
                $listeDevice[] = $lineDevice;
            }
            return $listeDevice;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function putAction($name, $action) {
        try {
            $arg = "f";
            if (strtolower($action) == "true") {
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
}