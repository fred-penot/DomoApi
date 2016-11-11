<?php
namespace DomoApi\Services;

class Japscan {
    private $db = null;
    private $log = null;
    private $dirSrc;
    private $dirDest;
    private $dirPdf;
    private $wget;
    private $imageMagik;

    public function __construct($db, $log, $dirSrc, $dirDest, $dirPdf, $wget, $imageMagik) {
        $this->db = $db;
        $this->log = $log;
        $this->dirSrc = $dirSrc;
        $this->dirDest = $dirDest;
        $this->dirPdf = $dirPdf;
        $this->wget = $wget;
        $this->imageMagik = $imageMagik;
    }

    public function __destruct() {}

    public function generate($urlEncode, $tomeMin, $tomeMax, $pageMin, $pageMax) {
        try {
            set_time_limit(0);
            $checkDirectories = $this->checkDirectories();
            if ($checkDirectories instanceof \Exception) {
                throw new \Exception($checkDirectories->getMessage());
            }
            $timestampIn = time();
            // $urlDecode = str_replace(' ', '%20', urldecode($urlEncode));
            for ($tome = $tomeMin; $tome <= $tomeMax; $tome ++) {
                $aspireTome = $this->aspireTome2($urlEncode, $tome, $pageMin, $pageMax);
                if ($aspireTome instanceof \Exception) {
                    throw new \Exception($aspireTome->getMessage());
                }
                $imageToPdf = $this->imageToPdf2("tome" . $tome . ".pdf");
                if ($imageToPdf instanceof \Exception) {
                    throw new \Exception($imageToPdf->getMessage());
                }
                $cleanDest = $this->cleanDest();
                if ($cleanDest instanceof \Exception) {
                    throw new \Exception($cleanDest->getMessage());
                }
            }
            $timestamp = time() - $timestampIn;
            return $timestamp;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function checkDirectories() {
        try {
            if (! is_dir($this->dirSrc)) {
                if (! mkdir($this->dirSrc, 0777, true)) {
                    throw new \Exception('Echec lors de la création du répertoire ' . $this->dirSrc);
                }
            }
            if (! is_dir($this->dirDest)) {
                if (! mkdir($this->dirDest, 0777, true)) {
                    throw new \Exception('Echec lors de la création du répertoire ' . $this->dirDest);
                }
            }
            if (! is_dir($this->dirPdf)) {
                if (! mkdir($this->dirPdf, 0777, true)) {
                    throw new \Exception('Echec lors de la création du répertoire ' . $this->dirPdf);
                }
            }
            return true;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    private function aspireTome($tomeId, $downloadId) {
        try {
            $sql = "SELECT * FROM manga_chapter where manga_tome_id=" . $tomeId . ";";
            $chapters = $this->db->fetchAll($sql);
            $numPageDecode = 0;
            $nbChapter = count($chapters);
            foreach ($chapters as $chapter) {
                if (! is_dir($this->dirSrc . DIRECTORY_SEPARATOR . $chapter['id'])) {
                    if (! mkdir($this->dirSrc . DIRECTORY_SEPARATOR . $chapter['id'], 0777, true)) {
                        throw new \Exception(
                            'Echec lors de la création du répertoire ' . $this->dirSrc .
                                 DIRECTORY_SEPARATOR . $chapter['id']);
                    }
                }
                $sql = "SELECT * FROM manga_ebook where manga_chapter_id=" . $chapter['id'] . ";";
                $results = $this->db->fetchAll($sql);
                $urlDecode = str_replace(' ', '%20', 
                    $results[0]['url_mask'] . str_replace('__FORMAT__', $results[0]['format'], 
                        $results[0]['page_mask']));
                
                $pageMin = (int) $results[0]['page_min'];
                $pageMax = (int) $results[0]['page_max'];
                
                for ($page = $pageMin; $page <= $pageMax; $page ++) {
                    if ($pageMax < 100) {
                        if ($page < 10) {
                            $page = '0' . $page;
                        }
                    } else {
                        if ($page < 10) {
                            $page = '00' . $page;
                        } elseif ($page < 100) {
                            $page = '0' . $page;
                        }
                    }
                    $currentUrl = str_replace(
                        array(
                            '__PAGE__',
                            '/cr-images/'
                        ), 
                        array(
                            $page,
                            '/lel/'
                        ), $urlDecode);
                    
                    $fileTmp = $this->dirSrc . DIRECTORY_SEPARATOR . $chapter['id'] .
                         DIRECTORY_SEPARATOR . $page . '.jpg';
                    $fileEnd = $this->dirDest . DIRECTORY_SEPARATOR . $chapter['id'] .
                         DIRECTORY_SEPARATOR . $page . '.jpg';
                    if (! is_dir($this->dirDest . DIRECTORY_SEPARATOR . $chapter['id'])) {
                        if (! mkdir($this->dirDest . DIRECTORY_SEPARATOR . $chapter['id'], 0777, 
                            true)) {
                            throw new \Exception(
                                'Echec lors de la création du répertoire ' . $this->dirDest .
                                     DIRECTORY_SEPARATOR . $chapter['id']);
                        }
                    }
                    if ($this->wget) {
                        exec(
                            'wget -P ' . $this->dirSrc . DIRECTORY_SEPARATOR . $chapter['id'] . ' ' .
                                 $currentUrl);
                        if (file_exists($fileTmp)) {
                            // $decode = $this->decodeImageJpg($fileTmp, $chapter['id'], $nbChapter);
                            copy($fileTmp, $fileEnd);
                            unlink($fileTmp);
                        }
                    } else {
                        $current = imagecreatefromjpeg($currentUrl);
                        if ($current) {
                            imagejpeg($current, $fileTmp);
                            imagedestroy($current);
                            // $decode = $this->decodeImageJpg($fileTmp, $chapter['id'], $nbChapter);
                            copy($fileTmp, $fileEnd);
                            unlink($fileTmp);
                        }
                    }
                    $numPageDecode ++;
                    $this->updateMangaDownload($downloadId, $numPageDecode, 'decode');
                }
                rmdir($this->dirSrc . DIRECTORY_SEPARATOR . $chapter['id']);
                $nbChapter --;
            }
            gc_collect_cycles();
            return true;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    private function aspireChapter($url, $pageMin, $pageMax, $downloadId) {
        try {
            for ($page = $pageMin; $page <= $pageMax; $page ++) {
                if ($pageMax < 100) {
                    if ($page < 10) {
                        $page = '0' . $page;
                    }
                } else {
                    if ($page < 10) {
                        $page = '00' . $page;
                    } elseif ($page < 100) {
                        $page = '0' . $page;
                    }
                }
                $currentUrl = str_replace(
                    array(
                        '__PAGE__',
                        '/cr-images/'
                    ), 
                    array(
                        $page,
                        '/lel/'
                    ), $url);
                $fileTmp = $this->dirSrc . DIRECTORY_SEPARATOR . $page . '.jpg';
                $fileEnd = $this->dirDest . DIRECTORY_SEPARATOR . $page . '.jpg';
                if ($this->wget) {
                    exec('wget -P ' . $this->dirSrc . ' ' . $currentUrl);
                    if (file_exists($fileTmp)) {
                        // $decode = $this->decodeImageJpg($fileTmp);
                        copy($fileTmp, $fileEnd);
                        unlink($fileTmp);
                    }
                } else {
                    $current = imagecreatefromjpeg($currentUrl);
                    if ($current) {
                        imagejpeg($current, $fileTmp);
                        imagedestroy($current);
                        // $decode = $this->decodeImageJpg($fileTmp);
                        copy($fileTmp, $fileEnd);
                        unlink($fileTmp);
                    }
                }
                $this->updateMangaDownload($downloadId, $page, 'decode');
            }
            gc_collect_cycles();
            return true;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    private function updateMangaDownload($downloadId, $currentPage, $type) {
        try {
            if ($type == 'decode') {
                $queryUpdate = 'UPDATE manga_download SET current_page_decode=' . $currentPage .
                     ' WHERE id=' . $downloadId . ';';
            } elseif ($type == 'pdf') {
                $queryUpdate = 'UPDATE manga_download SET current_page_pdf=' . $currentPage .
                     ' WHERE id=' . $downloadId . ';';
            }
            $result = $this->db->exec($queryUpdate);
            return true;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    private function decodeImageJpg($fileTmp, $chapterId = 0, $nbChapter = 0) {
        try {
            if ($chapterId > 0) {
                if ($nbChapter < 10) {
                    $dirChapter = '000' . $nbChapter . '_' . $chapterId;
                } elseif ($nbChapter < 100) {
                    $dirChapter = '00' . $nbChapter . '_' . $chapterId;
                } elseif ($nbChapter < 1000) {
                    $dirChapter = '0' . $nbChapter . '_' . $chapterId;
                } else {
                    $dirChapter = $nbChapter . '_' . $chapterId;
                }
                if (! is_dir($this->dirDest . DIRECTORY_SEPARATOR . $dirChapter)) {
                    if (! mkdir($this->dirDest . DIRECTORY_SEPARATOR . $dirChapter, 0777, true)) {
                        throw new \Exception(
                            'Echec lors de la création du répertoire ' . $this->dirDest .
                                 DIRECTORY_SEPARATOR . $dirChapter);
                    }
                }
                $destFile = $this->dirDest . DIRECTORY_SEPARATOR . $dirChapter . DIRECTORY_SEPARATOR .
                     basename($fileTmp);
            } else {
                $destFile = $this->dirDest . DIRECTORY_SEPARATOR . basename($fileTmp);
            }
            $fileInfo = getimagesize($fileTmp);
            $finalWidth = $fileInfo[0];
            $finalHeight = $fileInfo[1];
            $dest = imagecreatetruecolor($finalWidth, $finalHeight);
            $src = imagecreatefromjpeg($fileTmp);
            $minLine = 0;
            $maxLine = 5;
            $partWidth = intval($finalWidth / $maxLine);
            $partHeight = intval($finalHeight / $maxLine);
            for ($line = $minLine; $line < $maxLine; $line ++) {
                $destX = 0;
                $destY = ($finalHeight / $maxLine) * $line;
                $srcYCut = $maxLine - 1 - $line;
                $srcY = intval(($finalHeight / $maxLine) * $srcYCut);
                for ($i = 0; $i < $maxLine; $i ++) {
                    if ($i == 0) {
                        $srcX = $partWidth * 2;
                    } elseif ($i == 1) {
                        $srcX = $partWidth * 4;
                    } elseif ($i == 2) {
                        $srcX = $partWidth * 0;
                    } elseif ($i == 3) {
                        $srcX = $partWidth * 3;
                    } elseif ($i == 4) {
                        $srcX = $partWidth * 1;
                    }
                    imagecopy($dest, $src, $destX, $destY, $srcX, $srcY, $finalWidth, $finalHeight);
                    $destX += $partWidth;
                }
            }
            imagejpeg($dest, $destFile);
            imagedestroy($dest);
            imagedestroy($src);
            gc_collect_cycles();
            return true;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    private function imageToPdf($pdfName, $downloadId, $isTome = false) {
        try {
            if ($this->imageMagik) {
                exec(
                    'convert ' . $this->dirDest . DIRECTORY_SEPARATOR . '*.jpg ' . $this->dirPdf .
                         DIRECTORY_SEPARATOR . $pdfName);
            } else {
                $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
                $pdf->SetAutoPageBreak(false, 0);
                if ($isTome) {
                    $numImage = 1;
                    $directories = scandir($this->dirDest);
                    foreach ($directories as $directory) {
                        if ($directory != '.' && $directory != '..') {
                            $images = scandir($this->dirDest . DIRECTORY_SEPARATOR . $directory);
                            foreach ($images as $image) {
                                if ($image != '.' && $image != '..') {
                                    $imageInfo = getimagesize(
                                        $this->dirDest . DIRECTORY_SEPARATOR . $directory .
                                             DIRECTORY_SEPARATOR . $image);
                                    $width = $imageInfo[0];
                                    $height = $imageInfo[1];
                                    if ($width > $height) {
                                        $size = array(
                                            297,
                                            210
                                        );
                                        $pdf->AddPage('L');
                                    } else {
                                        $size = array(
                                            210,
                                            297
                                        );
                                        $pdf->AddPage('P');
                                    }
                                    $pdf->Image(
                                        $this->dirDest . DIRECTORY_SEPARATOR . $directory .
                                             DIRECTORY_SEPARATOR . $image, 0, 0, $size[0], $size[1], 
                                            '', '', '', true, 300, '', false, false, 0);
                                    $pdf->setPageMark();
                                    $this->updateMangaDownload($downloadId, $numImage, 'pdf');
                                    $numImage ++;
                                }
                            }
                        }
                    }
                    $pdf->Output($this->dirPdf . DIRECTORY_SEPARATOR . $pdfName, 'F');
                } else {
                    $images = scandir($this->dirDest);
                    $numImage = 1;
                    foreach ($images as $image) {
                        if ($image != '.' && $image != '..') {
                            $imageInfo = getimagesize($this->dirDest . DIRECTORY_SEPARATOR . $image);
                            $width = $imageInfo[0];
                            $height = $imageInfo[1];
                            if ($width > $height) {
                                $size = array(
                                    297,
                                    210
                                );
                                $pdf->AddPage('L');
                            } else {
                                $size = array(
                                    210,
                                    297
                                );
                                $pdf->AddPage('P');
                            }
                            $pdf->Image($this->dirDest . DIRECTORY_SEPARATOR . $image, 0, 0, 
                                $size[0], $size[1], '', '', '', true, 300, '', false, false, 0);
                            $pdf->setPageMark();
                            $this->updateMangaDownload($downloadId, $numImage, 'pdf');
                            $numImage ++;
                        }
                    }
                    $pdf->Output($this->dirPdf . DIRECTORY_SEPARATOR . $pdfName, 'F');
                }
            }
            gc_collect_cycles();
            return true;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    private function imageToPdf2($pdfName) {
        try {
            if ($this->imageMagik) {
                exec(
                    'convert ' . $this->dirDest . DIRECTORY_SEPARATOR . '*.jpg ' . $this->dirPdf .
                         DIRECTORY_SEPARATOR . $pdf);
            } else {
                $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
                $pdf->SetAutoPageBreak(false, 0);
                $images = scandir($this->dirDest);
                foreach ($images as $image) {
                    if ($image != '.' && $image != '..') {
                        $imageInfo = getimagesize($this->dirDest . DIRECTORY_SEPARATOR . $image);
                        $width = $imageInfo[0];
                        $height = $imageInfo[1];
                        if ($width > $height) {
                            $size = array(
                                297,
                                210
                            );
                            $pdf->AddPage('L');
                        } else {
                            $size = array(
                                210,
                                297
                            );
                            $pdf->AddPage('P');
                        }
                        $pdf->Image($this->dirDest . DIRECTORY_SEPARATOR . $image, 0, 0, $size[0], 
                            $size[1], '', '', '', true, 300, '', false, false, 0);
                        $pdf->setPageMark();
                    }
                }
                $pdf->Output($this->dirPdf . DIRECTORY_SEPARATOR . $pdfName, 'F');
            }
            gc_collect_cycles();
            return true;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    private function cleanDest() {
        try {
            $elementsToDelete = scandir($this->dirDest);
            foreach ($elementsToDelete as $elementToDelete) {
                if ($elementToDelete != '.' && $elementToDelete != '..') {
                    if (is_dir($this->dirDest . DIRECTORY_SEPARATOR . $elementToDelete)) {
                        $dirToDelete = scandir(
                            $this->dirDest . DIRECTORY_SEPARATOR . $elementToDelete);
                        foreach ($dirToDelete as $fileToDelete) {
                            if ($fileToDelete != '.' && $fileToDelete != '..') {
                                unlink(
                                    $this->dirDest . DIRECTORY_SEPARATOR . $elementToDelete .
                                         DIRECTORY_SEPARATOR . $fileToDelete);
                            }
                        }
                        rmdir($this->dirDest . DIRECTORY_SEPARATOR . $elementToDelete);
                    } else {
                        unlink($this->dirDest . DIRECTORY_SEPARATOR . $elementToDelete);
                    }
                }
            }
            gc_collect_cycles();
            return true;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    private function cleanSrc() {
        try {
            $elementsToDelete = scandir($this->dirSrc);
            foreach ($elementsToDelete as $elementToDelete) {
                if ($elementToDelete != '.' && $elementToDelete != '..') {
                    if (is_dir($this->dirSrc . DIRECTORY_SEPARATOR . $elementToDelete)) {
                        $dirToDelete = scandir(
                            $this->dirSrc . DIRECTORY_SEPARATOR . $elementToDelete);
                        foreach ($dirToDelete as $fileToDelete) {
                            if ($fileToDelete != '.' && $fileToDelete != '..') {
                                unlink(
                                    $this->dirSrc . DIRECTORY_SEPARATOR . $elementToDelete .
                                         DIRECTORY_SEPARATOR . $fileToDelete);
                            }
                        }
                        rmdir($this->dirSrc . DIRECTORY_SEPARATOR . $elementToDelete);
                    } else {
                        unlink($this->dirSrc . DIRECTORY_SEPARATOR . $elementToDelete);
                    }
                }
            }
            gc_collect_cycles();
            return true;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function grabMangaTitle() {
        try {
            set_time_limit(0);
            $countInsertItem = 0;
            $flux = file_get_contents('http://www.japscan.com/mangas/');
            $partSearchBegin = '<div class="row"><div class="cell"><a href="';
            $partSearchEnd = '</a></div><div class="cell">';
            $explode1 = explode($partSearchBegin, $flux);
            $nbElement = 0;
            foreach ($explode1 as $element) {
                if ($nbElement > 0) {
                    $explode2 = explode($partSearchEnd, $element);
                    $explode3 = explode('">', $explode2[0]);
                    if (count($explode3) > 0) {
                        $queryCheck = 'SELECT * FROM manga WHERE title="' . trim($explode3[1]) . '";';
                        $result = $this->db->fetchAll($queryCheck);
                        if (count($result) == 0) {
                            $queryInsert = 'INSERT INTO manga (title, url) VALUES ("' .
                                 trim($explode3[1]) . '", "http://www.japscan.com' .
                                 trim($explode3[0]) . '");';
                            $result = $this->db->exec($queryInsert);
                            if (! $result) {
                                throw new \Exception(
                                    "Erreur lors de l'injection de " . trim($explode3[1]) . ".");
                            }
                            $countInsertItem ++;
                        }
                    }
                }
                $nbElement ++;
            }
            return $countInsertItem;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function grabMangaTomeAndChapter() {
        try {
            set_time_limit(0);
            $countInsertTome = 0;
            $countInsertChapter = 0;
            $mangas = $this->getAllManga();
            if ($mangas instanceof \Exception) {
                throw new \Exception($mangas->getMessage());
            }
            foreach ($mangas as $manga) {
                list ($countCurrentInsertTome, $countCurrentInsertChapter) = $this->insertMangaChapter(
                    $manga);
                $countInsertTome += $countCurrentInsertTome;
                $countInsertChapter += $countCurrentInsertChapter;
            }
            return array(
                $countInsertTome,
                $countInsertChapter
            );
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function grabMangaEbook() {
        try {
            set_time_limit(0);
            $countInsertEbook = 0;
            $mangaChapters = $this->getAllMangaChapter();
            if ($mangaChapters instanceof \Exception) {
                throw new \Exception($mangaChapters->getMessage());
            }
            /*$mangaChapters = array();
            $mangaChapter = array(
                'id' => 140905,
                'manga_id' => 321,
                'manga_tome_id' => 50663,
                'title' => 'Scan Dragon Ball Tome 42 VF',
                'url' => 'http://www.japscan.com/lecture-en-ligne/dragon-ball/volume-42/'
            );
            $mangaChapters[] = $mangaChapter;
            $mangaChapter = array(
                'id' => 141029,
                'manga_id' => 332,
                'manga_tome_id' => 50764,
                'title' => 'Scan Dragon Ball Multiverse 50 VF : Univers 13 - Deux frères',
                'url' => 'http://www.japscan.com/lecture-en-ligne/dragon-ball-multiverse/50/'
            );
            $mangaChapters[] = $mangaChapter;*/
            foreach ($mangaChapters as $mangaChapter) {
                $insertMangaEbook = $this->insertMangaEbook($mangaChapter);
                if ($insertMangaEbook instanceof \Exception) {
                    throw new \Exception($insertMangaEbook->getMessage());
                }
                if ($insertMangaEbook) {
                    $countInsertEbook ++;
                }
            }
            return $countInsertEbook;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function dropSaveTables() {
        try {
            $queryDrop = "DROP TABLE IF EXISTS save_manga_ebook ;";
            $this->db->exec($queryDrop);
            $queryDrop = "DROP TABLE IF EXISTS save_manga_chapter ;";
            $this->db->exec($queryDrop);
            $queryDrop = "DROP TABLE IF EXISTS save_manga_tome ;";
            $this->db->exec($queryDrop);
            $queryDrop = "DROP TABLE IF EXISTS save_manga ;";
            $this->db->exec($queryDrop);
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function saveTableManga() {
        try {
            $queryCreate = "CREATE TABLE IF NOT EXISTS `save_manga` (
                              `id` INT(11) NOT NULL AUTO_INCREMENT,
                              `title` VARCHAR(255) NOT NULL,
                              `url` VARCHAR(255) NOT NULL,
                              `synopsis` TEXT NULL DEFAULT NULL,
                              PRIMARY KEY (`id`))
                            ENGINE = InnoDB
                            AUTO_INCREMENT = 1
                            DEFAULT CHARACTER SET = utf8;";
            $this->db->exec($queryCreate);
            $queryInsert = "INSERT INTO save_manga (SELECT * FROM manga);";
            $result = $this->db->exec($queryInsert);
            if (! $result) {
                throw new \Exception("Erreur lors de la sauvegarde de la table <manga>.");
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function saveTableMangaTome() {
        try {
            $queryCreate = "CREATE TABLE IF NOT EXISTS `save_manga_tome` (
                              `id` INT(11) NOT NULL AUTO_INCREMENT,
                              `manga_id` INT(11) NOT NULL,
                              `title` VARCHAR(255) NOT NULL,
                              PRIMARY KEY (`id`),
                              INDEX `fk_save_manga_tome_manga1_idx` (`manga_id` ASC),
                              CONSTRAINT `fk_save_manga_tome_manga1`
                                FOREIGN KEY (`manga_id`)
                                REFERENCES `save_manga` (`id`)
                                ON DELETE NO ACTION
                                ON UPDATE NO ACTION)
                            ENGINE = InnoDB
                            AUTO_INCREMENT = 1
                            DEFAULT CHARACTER SET = utf8;";
            $this->db->exec($queryCreate);
            $queryInsert = "INSERT INTO save_manga_tome (SELECT * FROM manga_tome);";
            $result = $this->db->exec($queryInsert);
            if (! $result) {
                throw new \Exception("Erreur lors de la sauvegarde de la table <manga_tome>.");
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function saveTableMangaChapter() {
        try {
            $queryCreate = "CREATE TABLE IF NOT EXISTS `save_manga_chapter` (
                              `id` INT(11) NOT NULL AUTO_INCREMENT,
                              `manga_id` INT(11) NOT NULL,
                              `manga_tome_id` INT(11) NULL,
                              `title` VARCHAR(255) NOT NULL,
                              `url` VARCHAR(255) NOT NULL,
                              PRIMARY KEY (`id`),
                              INDEX `fk_save_manga_chapter_mangas1_idx` (`manga_id` ASC),
                              INDEX `fk_save_manga_chapter_manga_tome1_idx` (`manga_tome_id` ASC),
                              CONSTRAINT `fk_save_manga_chapter_mangas1`
                                FOREIGN KEY (`manga_id`)
                                REFERENCES `save_manga` (`id`)
                                ON DELETE NO ACTION
                                ON UPDATE NO ACTION,
                              CONSTRAINT `fk_save_manga_chapter_manga_tome1`
                                FOREIGN KEY (`manga_tome_id`)
                                REFERENCES `save_manga_tome` (`id`)
                                ON DELETE NO ACTION
                                ON UPDATE NO ACTION)
                            ENGINE = InnoDB
                            AUTO_INCREMENT = 1
                            DEFAULT CHARACTER SET = utf8;";
            $this->db->exec($queryCreate);
            $queryInsert = "INSERT INTO save_manga_chapter (SELECT * FROM manga_chapter);";
            $result = $this->db->exec($queryInsert);
            if (! $result) {
                throw new \Exception("Erreur lors de la sauvegarde de la table <manga_chapter>.");
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function saveTableMangaEbook() {
        try {
            $queryCreate = "CREATE TABLE IF NOT EXISTS `save_manga_ebook` (
                              `id` INT(11) NOT NULL AUTO_INCREMENT,
                              `manga_chapter_id` INT(11) NOT NULL,
                              `url_mask` VARCHAR(255) NOT NULL,
                              `page_min` VARCHAR(255) NOT NULL,
                              `page_max` VARCHAR(255) NOT NULL,
                              `page_mask` VARCHAR(255) NOT NULL,
                              `format` VARCHAR(4) NOT NULL,
                              PRIMARY KEY (`id`),
                              INDEX `fk_save_manga_ebook_manga_chapter1_idx` (`manga_chapter_id` ASC),
                              CONSTRAINT `fk_save_manga_ebook_manga_chapter1`
                                FOREIGN KEY (`manga_chapter_id`)
                                REFERENCES `save_manga_chapter` (`id`)
                                ON DELETE NO ACTION
                                ON UPDATE NO ACTION)
                            ENGINE = InnoDB
                            AUTO_INCREMENT = 1
                            DEFAULT CHARACTER SET = utf8;";
            $this->db->exec($queryCreate);
            $queryInsert = "INSERT INTO save_manga_ebook (SELECT * FROM manga_ebook);";
            $result = $this->db->exec($queryInsert);
            if (! $result) {
                throw new \Exception("Erreur lors de la sauvegarde de la table <manga_ebook>.");
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function setMangaAction($save = 0, $maj = 0) {
        try {
            $sql = "UPDATE manga_action SET save=" . $save . ", maj=" . $maj . ";";
            $this->db->exec($sql);
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function checkSaveOk() {
        try {
            $sql = "SELECT * from manga_action WHERE save=1;";
            $save = $this->db->fetchAll($sql);
            if (count($save) > 0) {
                return true;
            }
            return false;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function insertMangaChapter($manga) {
        try {
            $insertTome = 0;
            $insertChapter = 0;
            $flux = file_get_contents($manga['url']);
            $partSearchBegin = '<h2 class="bg-header">Liste Des Chapitres</h2>' . "\n" .
                 '<div id="liste_chapitres">';
            list ($partSynopis, $partChapterAndTomeList) = explode($partSearchBegin, $flux);
            // synopsys
            if (strstr($partSynopis, '<div id="synopsis">') !== false) {
                list ($drop, $synopsisToClean) = explode('<div id="synopsis">', $partSynopis);
                $synopsis = str_replace('"', '', trim(str_replace('</div>', '', $synopsisToClean)));
                try {
                    $queryUpdate = 'UPDATE manga SET synopsis="' . $synopsis . '" WHERE id=' .
                         $manga['id'] . ' ;';
                    $result = $this->db->exec($queryUpdate);
                } catch (\Exception $ex) {
                    $this->log->addError($ex->getMessage());
                }
            }
            $partSearchEnd = '</a>' . "\n" . '</li>' . "\n" . '</ul>';
            $chapterAndTomeList = explode($partSearchEnd, $partChapterAndTomeList);
            foreach ($chapterAndTomeList as $chapterAndTome) {
                $idTome = 0;
                // tome éventuel
                if (substr(trim($chapterAndTome), 0, 4) == '<h2>') {
                    list ($tomeTitleToClean) = explode('</h2>', trim($chapterAndTome));
                    $tomeTitleTmp = trim(str_replace('<h2>', '', trim($tomeTitleToClean)));
                    $tomeTitle = trim(str_replace('"', '', trim($tomeTitleTmp)));
                    try {
                        $queryCheck = 'SELECT * FROM manga_tome WHERE manga_id=' . $manga['id'] .
                             ' and title="' . $tomeTitle . '";';
                        $result = $this->db->fetchAll($queryCheck);
                        if (count($result) == 0) {
                            $queryInsert = 'INSERT INTO manga_tome (manga_id, title) VALUES (' .
                                 $manga['id'] . ', "' . $tomeTitle . '");';
                            $result = $this->db->exec($queryInsert);
                            if (! $result) {
                                $this->log->addError(
                                    "Erreur lors de l'insertion du tome " . $tomeTitle . " du manga " .
                                         $manga['id']);
                                $idTome = 0;
                            } else {
                                $idTome = $this->db->lastInsertId();
                            }
                            $insertTome ++;
                        }
                    } catch (\Exception $ex) {
                        $this->log->addError($ex->getMessage());
                    }
                }
                
                $chapterList = explode('<li>' . "\n" . '<a href="', $chapterAndTome);
                if (count($chapterList) > 1) {
                    foreach ($chapterList as $chapterToClean) {
                        $urlAndTitleChapterToClean = explode('">', $chapterToClean);
                        if (count($urlAndTitleChapterToClean) > 1) {
                            list ($urlChapterToClean, $titleChapterToClean) = $urlAndTitleChapterToClean;
                            if (strstr($urlChapterToClean, '//www.japscan.com/lecture-en-ligne') !==
                                 false) {
                                $urlChapter = 'http:' . trim($urlChapterToClean);
                                $titleChapter = str_replace('"', '', 
                                    trim(
                                        trim(
                                            str_replace('</a>' . "\n" . '</li>', '', 
                                                $titleChapterToClean))));
                                try {
                                    if ($idTome > 0) {
                                        $queryCheck = 'SELECT * FROM manga_chapter WHERE manga_id=' .
                                             $manga['id'] . ' and manga_tome_id=' . $idTome .
                                             ' and title="' . $titleChapter . '" and url="' .
                                             $urlChapter . '";';
                                        $result = $this->db->fetchAll($queryCheck);
                                        if (count($result) == 0) {
                                            $queryInsert = 'INSERT INTO manga_chapter (manga_id, manga_tome_id, title, url) VALUES (' .
                                                 $manga['id'] . ', ' . $idTome . ', "' .
                                                 $titleChapter . '", "' . $urlChapter . '");';
                                            $result = $this->db->exec($queryInsert);
                                            if (! $result) {
                                                $this->log->addError(
                                                    "Erreur lors de l'insertion du chapitre " .
                                                         $titleChapter . " du manga " . $manga['id']);
                                            }
                                            $insertChapter ++;
                                        }
                                    } else {
                                        $queryCheck = 'SELECT * FROM manga_chapter WHERE manga_id=' .
                                             $manga['id'] . ' and title="' . $titleChapter .
                                             '" and url="' . $urlChapter . '";';
                                        $result = $this->db->fetchAll($queryCheck);
                                        if (count($result) == 0) {
                                            $queryInsert = 'INSERT INTO manga_chapter (manga_id, title, url) VALUES (' .
                                                 $manga['id'] . ', "' . $titleChapter . '", "' .
                                                 $urlChapter . '");';
                                            $result = $this->db->exec($queryInsert);
                                            if (! $result) {
                                                $this->log->addError(
                                                    "Erreur lors de l'insertion du chapitre " .
                                                         $titleChapter . " du manga " . $manga['id']);
                                            }
                                            $insertChapter ++;
                                        }
                                    }
                                } catch (\Exception $ex) {
                                    $this->log->addError($ex->getMessage());
                                }
                            }
                        }
                    }
                }
            }
            return array(
                $insertTome,
                $insertChapter
            );
        } catch (\Exception $ex) {
            $this->log->addError($ex->getMessage());
        }
    }

    private function insertMangaEbook($mangaChapter) {
        try {
            $insertChapter = false;
            $mangaChapterId = $mangaChapter['id'];
            $fluxToClean = file_get_contents($mangaChapter['url']);
            $flux = str_replace("\n", ' ', $fluxToClean);
            $partSearchBegin = '<select id="pages" name="pages">';
            list ($partBaseUrl, $partPages) = explode($partSearchBegin, $flux);
            $partSearchEnd = '</select>';
            list ($pageListToClean) = explode($partSearchEnd, trim($partPages));
            $pageList = explode('data-img="', trim($pageListToClean));
            $nbPage = 0;
            foreach ($pageList as $pageToClean) {
                $pageInfo = explode('" value="', trim($pageToClean));
                if (count($pageInfo) > 1) {
                    list ($page) = $pageInfo;
                    if ($nbPage == 0) {
                        $pageMinFind = $page;
                    }
                    $pageMaxFind = $page;
                    $nbPage ++;
                }
            }
            if ($nbPage > 0) {
                $format = substr(strrchr($pageMinFind, '.'), 1);
                $pageMin = str_replace('.' . $format, '', $pageMinFind);
                $pageMax = str_replace('.' . $format, '', $pageMaxFind);
                
                $pageMaskTemp = str_replace('.' . $format, '.' . '__FORMAT__', $pageMaxFind);
                $pageMask = str_replace(str_replace('.' . $format, '', $pageMaxFind) . '.', '__PAGE__' . '.', $pageMaskTemp);
                
                if (strstr($pageMask, '__FORMAT__') !== false) {
                    if (strstr($pageMask, '__PAGE__') !== false) {
                        list ($drop, $baseUrlToClean) = explode(
                            '<select name="mangas" id="mangas" ', trim($partBaseUrl));
                        list ($dataUrlNomToClean, $drop, $dataUrlTomeToClean) = explode(
                            '" data-uri="', trim($baseUrlToClean));
                        $dataUrlNom = trim(str_replace('data-nom="', '', trim($dataUrlNomToClean)));
                        
                        if (strstr($dataUrlTomeToClean, '" data-nom="') !== false) {
                            list ($drop, $dataUrlTomeToCleanTmp) = explode('" data-nom="', 
                                trim($dataUrlTomeToClean));
                            $dataUrlTome = trim(
                                str_replace('"></select>', '', trim($dataUrlTomeToCleanTmp)));
                        } else {
                            $dataUrlTome = trim(
                                str_replace('"></select>', '', trim($dataUrlTomeToClean)));
                        }
                        $urlMask = 'http://cdn.japscan.com/lel/' . $dataUrlNom . '/' .
                             $dataUrlTome . '/';
                        try {
                            $queryCheck = 'SELECT * FROM manga_ebook WHERE manga_chapter_id=' .
                                 $mangaChapterId . ';';
                            $result = $this->db->fetchAll($queryCheck);
                            if (count($result) == 0) {
                                $queryInsert = 'INSERT INTO manga_ebook (manga_chapter_id, url_mask, page_min, page_max, page_mask, format) VALUES (' .
                                     $mangaChapterId . ', "' . $urlMask . '", "' . trim($pageMin) .
                                     '", "' . trim($pageMax) . '", "' . trim($pageMask) . '", "' .
                                     $format . '");';
                                $result = $this->db->exec($queryInsert);
                                if (! $result) {
                                    $insertChapter = false;
                                } else {
                                    $insertChapter = true;
                                }
                            }
                        } catch (\Exception $e) {
                            $insertChapter = false;
                        }
                    }
                }
            }
            return $insertChapter;
        } catch (\Exception $ex) {
            $this->log->addError($ex->getMessage());
            return $ex;
        }
    }

    private function getAllManga() {
        try {
            $sql = "SELECT * FROM manga;";
            $mangas = $this->db->fetchAll($sql);
            return $mangas;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function getAllMangaTome() {
        try {
            $sql = "SELECT * FROM manga_tome;";
            $mangaTomes = $this->db->fetchAll($sql);
            return $mangaTomes;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function getAllMangaChapter() {
        try {
            $sql = "SELECT * FROM manga_chapter;";
            $mangaChapters = $this->db->fetchAll($sql);
            return $mangaChapters;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function deleteAllMangaEbook() {
        try {
            $queryDelete = "DELETE FROM manga_ebook ;";
            $result = $this->db->exec($queryDelete);
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function deleteAllMangaDownload() {
        try {
            $queryDelete = "DELETE FROM manga_download ;";
            $result = $this->db->exec($queryDelete);
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function deleteAllMangaTome() {
        try {
            $queryDelete = "DELETE FROM manga_tome ;";
            $result = $this->db->exec($queryDelete);
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function deleteAllMangaChapter() {
        try {
            $queryDelete = "DELETE FROM manga_chapter ;";
            $result = $this->db->exec($queryDelete);
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function grabTomeMangas() {
        try {
            set_time_limit(0);
            $deleteMangaDownload = $this->deleteAllMangaDownload();
            if ($deleteMangaDownload instanceof \Exception) {
                throw new \Exception($deleteMangaDownload->getMessage());
            }
            $deleteMangaEbook = $this->deleteAllMangaEbook();
            if ($deleteMangaEbook instanceof \Exception) {
                throw new \Exception($deleteMangaEbook->getMessage());
            }
            $deleteMangaChapter = $this->deleteAllMangaChapter();
            if ($deleteMangaChapter instanceof \Exception) {
                throw new \Exception($deleteMangaChapter->getMessage());
            }
            $deleteMangaTome = $this->deleteAllMangaTome();
            if ($deleteMangaTome instanceof \Exception) {
                throw new \Exception($deleteMangaTome->getMessage());
            }
            $mangas = $this->getAllManga();
            if ($mangas instanceof \Exception) {
                throw new \Exception($mangas->getMessage());
            }
            foreach ($mangas as $manga) {
                $this->insertMangaChapter($manga);
            }
            return $manga;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function grabEbookMangas() {
        try {
            set_time_limit(0);
            $deleteMangaEbook = $this->deleteAllMangaEbook();
            if ($deleteMangaEbook instanceof \Exception) {
                throw new \Exception($deleteMangaEbook->getMessage());
            }
            $mangaChapters = $this->getAllMangaChapter();
            if ($mangaChapters instanceof \Exception) {
                throw new \Exception($mangaChapters->getMessage());
            }
            foreach ($mangaChapters as $mangaChapter) {
                $this->insertMangaEbook($mangaChapter);
            }
            return $mangaChapter;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getMangasBySearch($search) {
        try {
            $mangas = array();
            $sql = "SELECT * FROM manga where UPPER(title) LIKE '%" . strtoupper($search) . "%';";
            $results = $this->db->fetchAll($sql);
            foreach ($results as $result) {
                $mangas[] = array(
                    "id" => $result['id'],
                    "title" => $result['title']
                );
            }
            return $mangas;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function getMangasBeginBy($search) {
        try {
            $mangas = array();
            $sql = "SELECT * FROM manga where UPPER(title) LIKE '" . strtoupper($search) . "%';";
            $results = $this->db->fetchAll($sql);
            foreach ($results as $result) {
                $mangas[] = array(
                    "id" => $result['id'],
                    "title" => $result['title']
                );
            }
            return $mangas;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function getMangasLike($search) {
        try {
            $mangas = array();
            $sql = "SELECT * FROM manga where UPPER(title) LIKE '" . strtoupper($search) . "%';";
            $results = $this->db->fetchAll($sql);
            foreach ($results as $result) {
                $mangas[] = array(
                    "id" => $result['id'],
                    "title" => $result['title']
                );
            }
            return $mangas;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function getTomeByTitle($title, $tome) {
        try {
            $sqlManga = "SELECT * FROM manga where UPPER(title) = '" . strtoupper($title) . "';";
            $resultManga = $this->db->fetchAll($sqlManga);
            $sqlTome = "SELECT * FROM manga_tome where manga_id = " . $resultManga[0]['id'] .
                 " ORDER BY id DESC;";
            $resultTome = $this->db->fetchAll($sqlTome);
            $tomeSelect = $tome - 1;
            return $resultTome[$tomeSelect];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getMangaById($id) {
        try {
            $sql = "SELECT * FROM manga where id=" . $id . ";";
            $results = $this->db->fetchAll($sql);
            return $results[0];
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function getMangaTomeList($mangaId) {
        try {
            $tomes = array();
            $sql = "SELECT * FROM manga_tome where manga_id=" . $mangaId . ";";
            $results = $this->db->fetchAll($sql);
            foreach ($results as $result) {
                $tomes[] = array(
                    "id" => $result['id'],
                    "title" => $result['title']
                );
            }
            return $tomes;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function getMangaTomeByChapter($chapterId) {
        try {
            $tomes = array();
            $sql = "SELECT * FROM manga_chapter where id=" . $chapterId . ";";
            $results = $this->db->fetchAll($sql);
            if ($results[0]['manga_tome_id']) {
                $sql = "SELECT * FROM manga_tome where id=" . $results[0]['manga_tome_id'] . ";";
                $result = $this->db->fetchAll($sql);
                if (count($result) > 0) {
                    $tomes[] = array(
                        "id" => $result[0]['id'],
                        "title" => $result[0]['title']
                    );
                }
            }
            return $tomes;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function getMangaChapterListByTome($tomeId) {
        try {
            $chapters = array();
            $sql = "SELECT * FROM manga_chapter where manga_tome_id=" . $tomeId . ";";
            $results = $this->db->fetchAll($sql);
            foreach ($results as $result) {
                $chapters[] = array(
                    "id" => $result['id'],
                    "title" => $result['title']
                );
            }
            return $chapters;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function getMangaChapterListByManga($mangaId) {
        try {
            $chapters = array();
            $sql = "SELECT * FROM manga_chapter where manga_id=" . $mangaId . ";";
            $results = $this->db->fetchAll($sql);
            foreach ($results as $result) {
                $chapters[] = array(
                    "id" => $result['id'],
                    "manga_tome_id" => $result['manga_tome_id'],
                    "title" => $result['title']
                );
            }
            return $chapters;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function checkArchiveManga($tomeId, $chapterId) {
        try {
            $archives = array();
            if ($tomeId > 0) {
                $sql = "SELECT * FROM manga_download where manga_tome_id=" . $tomeId . ";";
                $archives = $this->db->fetchAll($sql);
            } elseif ($chapterId > 0) {
                $sql = "SELECT * FROM manga_download where manga_chapter_id=" . $chapterId . ";";
                $archives = $this->db->fetchAll($sql);
            }
            if (count($archives) > 0) {
                return false;
            } else {
                return true;
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function insertMangaDownload($tomeId, $chapterId, $userId) {
        try {
            if ($tomeId > 0) {
                $sql = "SELECT * FROM manga_chapter where manga_tome_id=" . $tomeId . ";";
                $chapters = $this->db->fetchAll($sql);
                $maxPage = 0;
                foreach ($chapters as $chapter) {
                    $sql = "SELECT * FROM manga_ebook where manga_chapter_id=" . $chapter['id'] . ";";
                    $results = $this->db->fetchAll($sql);
                    $maxPage += (int) $results[0]['page_max'];
                }
                $queryInsert = 'INSERT INTO manga_download (manga_tome_id, user_id, max_page) VALUES (' .
                     $tomeId . ', ' . $userId . ', ' . $maxPage . ');';
                $result = $this->db->exec($queryInsert);
            } else {
                $sql = "SELECT * FROM manga_ebook where manga_chapter_id=" . $chapterId . ";";
                $results = $this->db->fetchAll($sql);
                $queryInsert = 'INSERT INTO manga_download (manga_chapter_id, user_id, max_page, finished) VALUES (' .
                     $chapterId . ', ' . $userId . ', "' . (int) $results[0]['page_max'] . '", 0);';
                $result = $this->db->exec($queryInsert);
            }
            return $this->db->lastInsertId();
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    private function tagFinished($downloadId) {
        try {
            $queryUpdate = 'UPDATE manga_download set current=0, finished=1 where id=' . $downloadId .
                 ';';
            $this->db->exec($queryUpdate);
            return true;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    private function tagCurrent($downloadId) {
        try {
            $queryUpdate = 'UPDATE manga_download set current=1 where id=' . $downloadId . ';';
            $this->db->exec($queryUpdate);
            return true;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function checkDownload($userId, $downloadId = 0) {
        try {
            $sql = 'SELECT * FROM manga_download where finished=0 and user_id=' . $userId .
                 ' and id<>' . $downloadId . ';';
            $results = $this->db->fetchAll($sql);
            if (count($results) > 0) {
                return true;
            }
            return false;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function getNextMangaDownload($userId) {
        try {
            $sql = "SELECT * FROM manga_download where user_id=" . $userId .
                 " and finished=0 ORDER BY id ASC ;";
            $results = $this->db->fetchAll($sql);
            $tomeId = 0;
            $chapterId = 0;
            if ($results[0]['manga_tome_id']) {
                $tomeId = $results[0]['manga_tome_id'];
            } else {
                $chapterId = $results[0]['manga_chapter_id'];
            }
            return array(
                $tomeId,
                $chapterId,
                $results[0]['id']
            );
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function getLastMangaDownload($tomeId, $chapterId) {
        try {
            if ($tomeId > 0) {
                $sql = "SELECT * FROM manga_download where manga_tome_id=" . $tomeId .
                     " ORDER BY id ASC ;";
            } else {
                $sql = "SELECT * FROM manga_download where manga_chapter_id=" . $chapterId .
                     " ORDER BY id ASC ;";
            }
            $results = $this->db->fetchAll($sql);
            return $results[0]['id'];
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function getLastMangaDownloadInfo($tomeId, $chapterId) {
        try {
            if ($tomeId > 0) {
                $sql = "SELECT * FROM manga_download where manga_tome_id=" . $tomeId .
                     " AND finished=0 ORDER BY id ASC ;";
            } else {
                $sql = "SELECT * FROM manga_download where manga_chapter_id=" . $chapterId .
                     " AND finished=0 ORDER BY id ASC ;";
            }
            $results = $this->db->fetchAll($sql);
            return $results[0];
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function getLastMangaDownloadInfoId($downloadId) {
        try {
            $sql = "SELECT * FROM manga_download where id=" . $downloadId . " ;";
            $results = $this->db->fetchAll($sql);
            return $results[0];
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function getLinkMangaDownload($tomeId, $chapterId) {
        try {
            if ($tomeId > 0) {
                $sql = "SELECT * FROM manga_tome where id=" . $tomeId . ";";
            } else {
                $sql = "SELECT * FROM manga_chapter where id=" . $chapterId . ";";
            }
            $results = $this->db->fetchAll($sql);
            $sql = "SELECT * FROM manga where id=" . $results[0]['manga_id'] . ";";
            $manga = $this->db->fetchAll($sql);
            return $this->dirPdf . DIRECTORY_SEPARATOR . str_replace(
                array(
                    ' ',
                    '"',
                    ':',
                    '/',
                    '?'
                ), 
                array(
                    '_',
                    '',
                    '_',
                    '.',
                    '.'
                ), $manga[0]['title'] . '_' . $results[0]['title']) . ".pdf";
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function generateManga($userId, $idTome, $idChapter, $idDownload) {
        try {
            set_time_limit(0);
            $timestampIn = time();
            $checkDirectories = $this->checkDirectories();
            if ($checkDirectories instanceof \Exception) {
                throw new \Exception($checkDirectories->getMessage());
            }
            $tomeId = $idTome;
            $chapterId = $idChapter;
            $downloadId = $idDownload;
            $waitingDownload = true;
            while ($waitingDownload) {
                $cleanSrc = $this->cleanSrc();
                if ($cleanSrc instanceof \Exception) {
                    throw new \Exception($cleanSrc->getMessage());
                }
                $tagCurrent = $this->tagCurrent($downloadId);
                if ($tagCurrent instanceof \Exception) {
                    throw new \Exception($tagCurrent->getMessage());
                }
                if ($tomeId > 0) {
                    $aspireTome = $this->aspireTome($tomeId, $downloadId);
                    if ($aspireTome instanceof \Exception) {
                        throw new \Exception($aspireTome->getMessage());
                    }
                    gc_collect_cycles();
                    $sql = "SELECT * FROM manga_tome where id=" . $tomeId . ";";
                    $results = $this->db->fetchAll($sql);
                    $sql = "SELECT * FROM manga where id=" . $results[0]['manga_id'] . ";";
                    $manga = $this->db->fetchAll($sql);
                    $imageToPdf = $this->imageToPdf(
                        str_replace(
                            array(
                                ' ',
                                '"',
                                ':',
                                '/',
                                '?'
                            ), 
                            array(
                                '_',
                                '',
                                '_',
                                '.',
                                '.'
                            ), $manga[0]['title'] . '_' . $results[0]['title']) . ".pdf", $downloadId, 
                        true);
                } else {
                    $sql = "SELECT * FROM manga_ebook where manga_chapter_id=" . $chapterId . ";";
                    $results = $this->db->fetchAll($sql);
                    $urlDecode = str_replace(' ', '%20', 
                        $results[0]['url_mask'] . str_replace('__FORMAT__', $results[0]['format'], 
                            $results[0]['page_mask']));
                    
                    $aspireTome = $this->aspireChapter($urlDecode, (int) $results[0]['page_min'], 
                        (int) $results[0]['page_max'], $downloadId);
                    if ($aspireTome instanceof \Exception) {
                        throw new \Exception($aspireTome->getMessage());
                    }
                    gc_collect_cycles();
                    $sql = "SELECT * FROM manga_chapter where id=" . $chapterId . ";";
                    $results = $this->db->fetchAll($sql);
                    $sql = "SELECT * FROM manga where id=" . $results[0]['manga_id'] . ";";
                    $manga = $this->db->fetchAll($sql);
                    $imageToPdf = $this->imageToPdf(
                        str_replace(
                            array(
                                ' ',
                                '"',
                                ':',
                                '/',
                                '?'
                            ), 
                            array(
                                '_',
                                '',
                                '_',
                                '.',
                                '.'
                            ), $manga[0]['title'] . '_' . $results[0]['title']) . ".pdf", $downloadId);
                }
                if ($imageToPdf instanceof \Exception) {
                    throw new \Exception($imageToPdf->getMessage());
                }
                $cleanDest = $this->cleanDest();
                if ($cleanDest instanceof \Exception) {
                    throw new \Exception($cleanDest->getMessage());
                }
                $tagFinished = $this->tagFinished($downloadId);
                if ($tagFinished instanceof \Exception) {
                    throw new \Exception($tagFinished->getMessage());
                }
                if ($this->checkDownload($userId)) {
                    list ($tomeId, $chapterId, $downloadId) = $this->getNextMangaDownload($userId);
                } else {
                    $waitingDownload = false;
                }
                gc_collect_cycles();
            }
            $timestamp = time() - $timestampIn;
            return $timestamp;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function getCurrrentMangaDownloads($userId) {
        try {
            $downloadManga = array();
            $sql = "SELECT * FROM manga_download where user_id=" . $userId .
                 " AND finished=0 ORDER BY id ASC;";
            $downloads = $this->db->fetchAll($sql);
            foreach ($downloads as $download) {
                $tomeId = 0;
                $chapterId = 0;
                if ($download['manga_tome_id']) {
                    $tomeId = $download['manga_tome_id'];
                    $sqlTome = "SELECT * FROM manga_tome where id=" . $tomeId . ";";
                    $bookResult = $this->db->fetchAll($sqlTome);
                    $title = 'Tome : ';
                } else {
                    $chapterId = $download['manga_chapter_id'];
                    $sqlChapter = "SELECT * FROM manga_chapter where id=" . $chapterId . ";";
                    $bookResult = $this->db->fetchAll($sqlChapter);
                    $title = 'Chap : ';
                }
                $title .= $bookResult[0]['title'];
                $sqlManga = "SELECT * FROM manga where id=" . $bookResult[0]['manga_id'] . ";";
                $manga = $this->db->fetchAll($sqlManga);
                $downloadManga[] = array(
                    "id" => $download['id'],
                    "tome_id" => $tomeId,
                    "chapter_id" => $chapterId,
                    "title" => $title,
                    "manga_id" => $bookResult[0]['manga_id'],
                    "manga_name" => $manga[0]['title'],
                    "current_page_decode" => $download['current_page_decode'],
                    "current_page_pdf" => $download['current_page_pdf'],
                    "max_page" => $download['max_page']
                );
            }
            return $downloadManga;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function getHistoryMangaDownload($userId) {
        try {
            $historyManga = array();
            $pdfList = array();
            $sql = "SELECT * FROM manga_download where user_id=" . $userId .
                 " AND finished=1 ORDER BY id DESC;";
            $histories = $this->db->fetchAll($sql);
            foreach ($histories as $history) {
                $tomeId = 0;
                $chapterId = 0;
                if ($history['manga_tome_id']) {
                    $tomeId = $history['manga_tome_id'];
                    $sqlTome = "SELECT * FROM manga_tome where id=" . $history['manga_tome_id'] . ";";
                    $bookResult = $this->db->fetchAll($sqlTome);
                    $title = 'Tome : ';
                } else {
                    $chapterId = $history['manga_chapter_id'];
                    $sqlChapter = "SELECT * FROM manga_chapter where id=" .
                         $history['manga_chapter_id'] . ";";
                    $bookResult = $this->db->fetchAll($sqlChapter);
                    $title = 'Chap : ';
                }
                $title .= $bookResult[0]['title'];
                $sqlManga = "SELECT * FROM manga where id=" . $bookResult[0]['manga_id'] . ";";
                $manga = $this->db->fetchAll($sqlManga);
                $pdfName = str_replace(
                    array(
                        ' ',
                        '"',
                        ':',
                        '/',
                        '?'
                    ), 
                    array(
                        '_',
                        '',
                        '_',
                        '.',
                        '.'
                    ), $manga[0]['title'] . '_' . $bookResult[0]['title']) . ".pdf";
                $pdfPath = $this->dirPdf . DIRECTORY_SEPARATOR . $pdfName;
                $size = filesize($pdfPath);
                if (file_exists($pdfPath) && ! in_array($pdfPath, $pdfList)) {
                    $pdfList[] = $pdfPath;
                    $historyManga[] = array(
                        "id" => $history['id'],
                        "title" => $title,
                        "size" => $size,
                        "manga_id" => $bookResult[0]['manga_id'],
                        "manga_name" => $manga[0]['title'],
                        "tome_id" => $tomeId,
                        "chapter_id" => $chapterId
                    );
                }
            }
            return $historyManga;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function deleteHistoryMangaDownload($historyId) {
        try {
            $sql = "SELECT * FROM manga_download where id=" . $historyId . " and current=0;";
            $history = $this->db->fetchAll($sql);
            if (count($history) > 0) {
                if ($history[0]['manga_tome_id']) {
                    $sqlTome = "SELECT * FROM manga_tome where id=" . $history[0]['manga_tome_id'] .
                         ";";
                    $bookResult = $this->db->fetchAll($sqlTome);
                } else {
                    $sqlChapter = "SELECT * FROM manga_chapter where id=" .
                         $history[0]['manga_chapter_id'] . ";";
                    $bookResult = $this->db->fetchAll($sqlChapter);
                }
                $sqlManga = "SELECT * FROM manga where id=" . $bookResult[0]['manga_id'] . ";";
                $manga = $this->db->fetchAll($sqlManga);
                $pdfName = str_replace(
                    array(
                        ' ',
                        '"',
                        ':',
                        '/',
                        '?'
                    ), 
                    array(
                        '_',
                        '',
                        '_',
                        '.',
                        '.'
                    ), $manga[0]['title'] . '_' . $bookResult[0]['title']) . ".pdf";
                $pdfPath = $this->dirPdf . DIRECTORY_SEPARATOR . $pdfName;
                if (file_exists($pdfPath)) {
                    if (unlink($pdfPath)) {
                        $queryDelete = "DELETE FROM manga_download where id=" . $historyId .
                             " and current=0;";
                        $this->db->exec($queryDelete);
                    } else {
                        return false;
                    }
                } else {
                    $queryDelete = "DELETE FROM manga_download where id=" . $historyId .
                         " and current=0;";
                    $this->db->exec($queryDelete);
                }
            }
            return true;
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }
}