<?php
namespace DomoApi\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SaveMangaCommand extends \Knp\Command\Command {

    protected function configure() {
        $this->setName("manga:save")->setDescription("Effectue la sauvegarde des mangas.");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        ini_set('memory_limit', '512M');
        $app = $this->getSilexApplication();
        $msg = "Lancement de la sauvegarde.";
        $app['monolog.manga.save']->addInfo($msg);
        $output->writeln($msg);
        try {
            $exec = $this->launch($output);
            if ($exec instanceof \Exception) {
                throw new \Exception($exec->getMessage());
            }
        } catch (\Exception $ex) {
            $app['monolog.manga.save']->addError($ex->getMessage());
            $app['monolog.manga.save']->addError($ex->getTraceAsString());
            $output->writeln("Une erreur s'est produite : " . $ex->getMessage());
        }
        $msg = "Sauvegarde terminée.";
        $app['monolog.manga.save']->addInfo($msg);
        $output->writeln($msg);
    }

    private function launch($output) {
        try {
            $app = $this->getSilexApplication();
            $dropTables = $app['service.japscan']->dropSaveTables();
            if ($dropTables instanceof \Exception) {
                throw new \Exception($dropTables->getMessage());
            }
            $saveTableManga = $app['service.japscan']->saveTableManga();
            if ($saveTableManga instanceof \Exception) {
                throw new \Exception($saveTableManga->getMessage());
            }
            $saveTableMangaTome = $app['service.japscan']->saveTableMangaTome();
            if ($saveTableMangaTome instanceof \Exception) {
                throw new \Exception($saveTableMangaTome->getMessage());
            }
            $saveTableMangaChapter = $app['service.japscan']->saveTableMangaChapter();
            if ($saveTableMangaChapter instanceof \Exception) {
                throw new \Exception($saveTableMangaChapter->getMessage());
            }
            $saveTableMangaEbook = $app['service.japscan']->saveTableMangaEbook();
            if ($saveTableMangaEbook instanceof \Exception) {
                throw new \Exception($saveTableMangaEbook->getMessage());
            }
            $setMangaAction = $app['service.japscan']->setMangaAction(1);
            if ($setMangaAction instanceof \Exception) {
                throw new \Exception($setMangaAction->getMessage());
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}