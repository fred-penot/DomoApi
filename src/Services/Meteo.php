<?php
namespace DomoApi\Services;

class Meteo {

    public function __construct() {
    }

    public function __destruct() {}

    public function getPrevision() {
        try {
            $url = 'http://www.infoclimat.fr/public-api/gfs/json?_ll=47.245191,-1.575853';
            $url .= '&_auth=BR8DFAB%2BV3UHKlViAnRXflkxVGEKfFVyAHwGZVs%2BBXgEbwNiUzNcOgBuVisBLgo8WHUGZQE6UmIGbVEpAHJePwVvA28Aa1cwB2hVMAItV3xZd1Q1CipVcgBrBmlbKAVnBG4DZ1MuXD8AbVYqATAKO1hqBnkBIVJrBmFRPgBoXjwFZgNmAGpXNwdgVSgCLVdmWWJUPAoxVTkAMQZnWz4FYgQxAzNTYlw%2FAGxWKgEwCjxYawZnATxSbgZiUTIAcl4iBR8DFAB%2BV3UHKlViAnRXflk%2FVGoKYQ%3D%3D&_c=53bf3fcf3261a06e4894aac17fd0325d';
            $infos = json_decode(file_get_contents($url), true);
            $previsions = [];
            if ($infos['request_state']==200) {
                $today = date('Y-m-d');
                $hourToday = [
                    $today.' 01:00:00',
                    $today.' 04:00:00',
                    $today.' 07:00:00',
                    $today.' 10:00:00',
                    $today.' 13:00:00',
                    $today.' 16:00:00',
                    $today.' 19:00:00',
                    $today.' 22:00:00',
                ];
                foreach ($hourToday as $hour) {
                    if ( isset($infos[$hour]) ) {
                        $prevision = [
                            't_degre' => round($infos[$hour]['temperature']['2m'] - 273.5),
                            'humidite' => round($infos[$hour]['humidite']['2m']),
                            'vent' => [
                                'moyen' => round($infos[$hour]['vent_moyen']['10m']),
                                'rafale' => round($infos[$hour]['vent_rafales']['10m']),
                            ],
                        ];
                        $previsions[str_replace($today.' ', '', $hour)] = $prevision;
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
