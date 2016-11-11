<?php
namespace DomoApi\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ForexAddCurrentCommand extends \Knp\Command\Command {

    protected function configure() {
        $this->setName("forex:add:current")->setDescription(
            "Ajout en continue des cotations en temps réel");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            ini_set('memory_limit', '256M');
            $app = $this->getSilexApplication();
            $msg = "Lancement de l'ajout des cotations courantes";
            $app['monolog.forex.current']->addInfo($msg);
            $output->writeln($msg);
            $memoryFree = $app['service.system']->freeMemory($app['parameter.command_free_memory']);
            if ($memoryFree instanceof \Exception) {
                throw new \Exception($memoryFree->getMessage());
            }
            $exec = $this->launch($output);
            if ($exec instanceof \Exception) {
                throw new \Exception($exec->getMessage());
            }
            $memoryFree = $app['service.system']->freeMemory($app['parameter.command_free_memory']);
            if ($memoryFree instanceof \Exception) {
                throw new \Exception($memoryFree->getMessage());
            }
        } catch (\Exception $ex) {
            $app['monolog.forex.current']->addError($ex->getMessage());
            $output->writeln("Une erreur s'est produite : " . $ex->getMessage());
        }
    }

    private function launch($output) {
        try {
            $app = $this->getSilexApplication();
            $error = 0;
            $countBeforeRefreshMemory = 0;
            while (true) {
                if ($countBeforeRefreshMemory > 100) {
                    $memoryFree = $app['service.system']->freeMemory(
                        $app['parameter.command_free_memory']);
                    if ($memoryFree instanceof \Exception) {
                        throw new \Exception($memoryFree->getMessage());
                    }
                }
                $idAuth = $app['service.forex']->getAuthId($app['parameter.forex.url.auth'], 
                    $app['parameter.forex.login'], $app['parameter.forex.password']);
                if ($idAuth instanceof \Exception) {
                    throw new \Exception($idAuth->getMessage());
                }
                $insert = $app['service.forex']->insertCurrentCotations(
                    $app['parameter.forex.url.cotations'], $idAuth);
                if ($insert instanceof \Exception) {
                    $error ++;
                    if ($error > 10) {
                        throw new \Exception($insert->getMessage());
                    }
                } else {
                    $error = 0;
                }
                $countBeforeRefreshMemory ++;
                gc_collect_cycles();
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}