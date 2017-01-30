<?php
namespace DomoApi\Services;

class Meteo {

    public function __construct() {
    }

    public function __destruct() {}

    public function getPrevision($latitude, $longitude, $timestamp) {
        try {
            $previsions = [];
            $url = 'http://www.infoclimat.fr/public-api/gfs/json?_ll='.$latitude.','.$longitude;
            $url .= '&_auth=BR8DFAB%2BV3UHKlViAnRXflkxVGEKfFVyAHwGZVs%2BBXgEbwNiUzNcOgBuVisBLgo8WHUGZQE6UmIGbVEpAHJePwVvA28Aa1cwB2hVMAItV3xZd1Q1CipVcgBrBmlbKAVnBG4DZ1MuXD8AbVYqATAKO1hqBnkBIVJrBmFRPgBoXjwFZgNmAGpXNwdgVSgCLVdmWWJUPAoxVTkAMQZnWz4FYgQxAzNTYlw%2FAGxWKgEwCjxYawZnATxSbgZiUTIAcl4iBR8DFAB%2BV3UHKlViAnRXflk%2FVGoKYQ%3D%3D&_c=53bf3fcf3261a06e4894aac17fd0325d';
            $infos = json_decode(file_get_contents($url), true);
            if (!isset($infos['request_state'])) {
                throw new \Exception('Erreur lors de l\'appel du webservice de prévision.');
            }
            if ($infos['request_state']!=200) {
                throw new \Exception('Erreur lors de la récupération des prévisions.');
            } else {
                $today = date('Y-m-d');
                $timestampToFind = date('Y-m-d H:i:s', $timestamp);
                $firstDate = new \DateTime($today.' 01:00:00');
                $firstTimestamp = $firstDate->getTimestamp();
                $hourToday = [
                    ['hour' => $today.' 01:00:00', 'timestamp1' => $firstTimestamp, 'timestamp2' => $firstTimestamp+(3600*2), 'hour2' => $today.' 04:00:00',],
                    ['hour' => $today.' 04:00:00', 'timestamp1' => $firstTimestamp+(3600*2), 'timestamp2' => $firstTimestamp+(3600*5), 'hour2' => $today.' 07:00:00',],
                    ['hour' => $today.' 07:00:00', 'timestamp1' => $firstTimestamp+(3600*5), 'timestamp2' => $firstTimestamp+(3600*8), 'hour2' => $today.' 10:00:00',],
                    ['hour' => $today.' 10:00:00', 'timestamp1' => $firstTimestamp+(3600*8), 'timestamp2' => $firstTimestamp+(3600*11), 'hour2' => $today.' 13:00:00',],
                    ['hour' => $today.' 13:00:00', 'timestamp1' => $firstTimestamp+(3600*11), 'timestamp2' => $firstTimestamp+(3600*14), 'hour2' => $today.' 16:00:00',],
                    ['hour' => $today.' 16:00:00', 'timestamp1' => $firstTimestamp+(3600*14), 'timestamp2' => $firstTimestamp+(3600*17), 'hour2' => $today.' 19:00:00',],
                    ['hour' => $today.' 19:00:00', 'timestamp1' => $firstTimestamp+(3600*17), 'timestamp2' => $firstTimestamp+(3600*20), 'hour2' => $today.' 22:00:00',],
                    ['hour' => $today.' 22:00:00', 'timestamp1' => $firstTimestamp+(3600*20), 'timestamp2' => $firstTimestamp+(3600*23), 'hour2' => null,],
                ];
                $tMin = null;
                $tMax = null;
                foreach ($hourToday as $time) {
                    $setCurrentPrevision = false;
                    if ( isset($infos[$time['hour']]) ) {
                        $prevision = [
                            't_degre' => round($infos[$time['hour']]['temperature']['2m'] - 273.5),
                            'humidite' => round($infos[$time['hour']]['humidite']['2m']),
                            'vent' => [
                                'moyen' => round($infos[$time['hour']]['vent_moyen']['10m']),
                                'rafale' => round($infos[$time['hour']]['vent_rafales']['10m']),
                            ],
                        ];
                        $previsions[str_replace($today.' ', '', $time['hour'])] = $prevision;
                    }
                    if ($timestamp > $time['timestamp1'] && $time['timestamp2'] && $timestamp < $time['timestamp2']) {
                        $tMin = $infos[$time['hour']]['temperature']['2m'];
                        $tMax = $infos[$time['hour2']]['temperature']['2m'];
                        $rapport = 1 - (($time['timestamp2']-$timestamp)/10800);
                        $tEcart = $infos[$time['hour2']]['temperature']['2m'] - $infos[$time['hour']]['temperature']['2m'];
                        $tCurrent = round(($tMin + ($tEcart*$rapport)) - 273.5);
                        $hEcart = $infos[$time['hour2']]['humidite']['2m'] - $infos[$time['hour']]['humidite']['2m'];
                        $hCurrent = round(($infos[$time['hour']]['humidite']['2m'] + ($hEcart*$rapport)));
                        $vMoyenEcart = $infos[$time['hour2']]['vent_moyen']['10m'] - $infos[$time['hour']]['vent_moyen']['10m'];
                        $vMoyenCurrent = round(($infos[$time['hour']]['vent_moyen']['10m'] + ($vMoyenEcart*$rapport)));
                        $vRafaleEcart = $infos[$time['hour2']]['vent_rafales']['10m'] - $infos[$time['hour']]['vent_rafales']['10m'];
                        $vRafaleCurrent = round(($infos[$time['hour']]['vent_rafales']['10m'] + ($vRafaleEcart*$rapport)));
                        $setCurrentPrevision = true;
                    } elseif ($timestamp < $time['timestamp1'] && !$tMin || $timestamp > $time['timestamp1'] && !$tMax) {
                        $tMin = $tMax = $infos[$time['hour']]['temperature']['2m'];
                        $tCurrent = round($tMin - 273.5);
                        $hCurrent = round($infos[$time['hour']]['humidite']['2m']);
                        $vMoyenCurrent = round($infos[$time['hour']]['vent_moyen']['10m']);
                        $vRafaleCurrent = round($infos[$time['hour']]['vent_rafales']['10m']);
                        $setCurrentPrevision = true;
                    }
                    if ($setCurrentPrevision) {
                        $prevision = [
                            't_degre' => $tCurrent,
                            'humidite' => $hCurrent,
                            'vent' => [
                                'moyen' => $vMoyenCurrent,
                                'rafale' => $vRafaleCurrent,
                            ],
                        ];
                        $previsions['current'] = $prevision;
                    }
                }
            }
            return $previsions;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getCities($word) {
        try {
            $url = 'http://www.cp-ville.com/cpcom.php?cpcommune='.$word;
            $result = file_get_contents($url);
            $json = trim(str_replace(["jsoncallback(", ");", "\n"], '', $result));
            $citiesAndCp = explode('{"ville":"', $json);
            $finalReturn = [];
            foreach ($citiesAndCp as $cityAndCp) {
                list($city, $cpToExtract) = explode('","cp":"', $cityAndCp);
                if ($cpToExtract) {
                    list($cp, $trash) = explode('"},"', $cpToExtract);
                    $finalReturn[] = [
                        'name' => $city,
                        'cp' => $cp,
                    ];
                }
            }
            return $finalReturn;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getCoordinatesCity($city, $cp) {
        try {
            $url = 'http://api-adresse.data.gouv.fr/search/?q='.$city;
            $infosCity = json_decode(file_get_contents($url), true);
            $finalReturn = [];
            foreach ($infosCity['features'] as $feature) {
                if ($feature['properties']['postcode'] == $cp) {
                    $finalReturn = $feature['geometry']['coordinates'];
                    break;
                }
            }
            return $finalReturn;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}
