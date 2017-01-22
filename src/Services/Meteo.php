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
}
