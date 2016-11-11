<?php
namespace DomoApi\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateMangaCommand extends \Knp\Command\Command {

    protected function configure() {
        $this->setName("manga:update")->setDescription("Mise à jour des mangas.");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        ini_set('memory_limit', '512M');
        $app = $this->getSilexApplication();
        $msg = "Lancement de la mise à jour.";
        $app['monolog.manga.update']->addInfo($msg);
        $output->writeln($msg);
        try {
            $exec = $this->launch($output);
            if ($exec instanceof \Exception) {
                throw new \Exception($exec->getMessage());
            }
        } catch (\Exception $ex) {
            $app['monolog.manga.update']->addError($ex->getMessage());
            $app['monolog.manga.update']->addError($ex->getTraceAsString());
            $output->writeln("Une erreur s'est produite : " . $ex->getMessage());
        }
        $msg = "Mise à jour terminée.";
        $app['monolog.manga.update']->addInfo($msg);
        $output->writeln($msg);
    }

    private function launch($output) {
        try {
            $app = $this->getSilexApplication();
            $checkSaveOk = $app['service.japscan']->checkSaveOk();
            if ($checkSaveOk instanceof \Exception) {
                throw new \Exception($checkSaveOk->getMessage());
            }
            if ($checkSaveOk) {
                $setMangaAction = $app['service.japscan']->setMangaAction();
                if ($setMangaAction instanceof \Exception) {
                    throw new \Exception($setMangaAction->getMessage());
                }
                $this->update($output);
            } else {
                throw new \Exception(
                    "Mise à jour impossible car la sauvegarde n'a pas été effectuée.");
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
    
    private function update($output) {
        try {
            $app = $this->getSilexApplication();
            $grabMangaTitle = $app['service.japscan']->grabMangaTitle();
            if ($grabMangaTitle instanceof \Exception) {
                throw new \Exception($grabMangaTitle->getMessage());
            }
            $output->writeln("titres ajoutés => ".$grabMangaTitle);
            $grabMangaTomeAndChapter = $app['service.japscan']->grabMangaTomeAndChapter();
            if ($grabMangaTomeAndChapter instanceof \Exception) {
                throw new \Exception($grabMangaTomeAndChapter->getMessage());
            }
            list($countTome, $countChapter) = $grabMangaTomeAndChapter;
            $output->writeln("tomes ajoutés => ".$countTome);
            $output->writeln("chapitres ajoutés => ".$countChapter);
            $grabMangaEbook = $app['service.japscan']->grabMangaEbook();
            if ($grabMangaEbook instanceof \Exception) {
                throw new \Exception($grabMangaEbook->getMessage());
            }
            $output->writeln("ebook ajoutés => ".$grabMangaEbook);
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}