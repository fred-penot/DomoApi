<?php
namespace DomoApi\Services;

use \Allocine\AlloHelper as AlloHelper;

class Allocine {

    public function searchMovie($title) {
        try {
            $data = array();
            $allocine = new AlloHelper();
            $titles = $allocine->search($title, 1);
            foreach ($titles['movie'] as $film) {
                $data[] = array(
                    "code" => $film['code'],
                    "title" => $film['title'],
                    "directors" => $film['castingShort']['directors'],
                    "posterUrl" => $film['posterURL'],
                    "productionYear" => $film['productionYear']
                );
            }
            return $data;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getMovie($code) {
        try {
            $allocine = new AlloHelper();
            $movieInfos = $allocine->movie($code);
            $data = array();
            $data['title'] = $movieInfos->title;
            $data['posterUrl'] = $movieInfos->poster->getImageHost() . '/' .
                 $movieInfos->poster->getImagePath();
            $elementToDelete = array(
                "<p>",
                "</p>",
                "<strong>",
                "</strong>",
                "<i>",
                "</i>",
                "<span>",
                "</span>",
                "<div>",
                "</div>",
                "<b>",
                "</b>",
                "<br>",
                "</br>",
                "<br />"
            );
            $data['synopsis'] = trim(str_replace($elementToDelete, "", $movieInfos->synopsis));
            $data['director'] = $movieInfos->castingShort->directors;
            $data['actor'] = $movieInfos->castingShort->actors;
            if (isset($movieInfos->release)) {
                $data['releaseDate'] = date("d/m/Y", strtotime($movieInfos->release->releaseDate));
            } else {
                $data['releaseDate'] = $movieInfos->productionYear;
            }
            $nationalities = array();
            foreach ($movieInfos->nationality as $nationality) {
                $nationalities[] = $nationality['$'];
            }
            $data['nationalities'] = $nationalities;
            $genres = array();
            foreach ($movieInfos->genre as $genre) {
                $genres[] = $genre['$'];
            }
            $data['genres'] = $genres;
            return $data;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function searchSerie($title) {
        try {
            $data = array();
            $allocine = new AlloHelper();
            $titles = $allocine->search($title, 1);
            foreach ($titles['tvseries'] as $serie) {
                $data[] = array(
                    "code" => $serie['code'],
                    "title" => $serie['originalTitle'],
                    "directors" => "",
                    "posterUrl" => $serie['poster']['href'],
                    "productionYear" => $serie['yearStart']
                );
            }
            return $data;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getSerie($code) {
        try {
            $allocine = new AlloHelper();
            $movieInfos = $allocine->tvserie($code);
            $data = array();
            $data['title'] = $movieInfos->title;
            $data['posterUrl'] = $movieInfos->poster['href'];
            $elementToDelete = array(
                "<p>",
                "</p>",
                "<strong>",
                "</strong>",
                "<i>",
                "</i>",
                "<span>",
                "</span>",
                "<div>",
                "</div>",
                "<b>",
                "</b>",
                "<br>",
                "</br>",
                "<br />"
            );
            $data['synopsis'] = trim(str_replace($elementToDelete, "", $movieInfos->synopsis));
            $data['director'] = $movieInfos->castingShort->directors;
            $data['actor'] = $movieInfos->castingShort->actors;
            $data['releaseDate'] = date("d/m/Y", strtotime($movieInfos->originalBroadcast['dateStart']));
            $nationalities = array();
            foreach ($movieInfos->nationality as $nationality) {
                $nationalities[] = $nationality['$'];
            }
            $data['nationalities'] = $nationalities;
            $genres = array();
            foreach ($movieInfos->genre as $genre) {
                $genres[] = $genre['$'];
            }
            $data['genres'] = $genres;
            return $data;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}