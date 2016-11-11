<?php
namespace DomoApi\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ForexAddArchiveCommand extends \Knp\Command\Command {

    protected function configure() {
        $this->setName("forex:add:archive")->setDescription(
            "Ajout d'une archive forex au format csv en base");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            ini_set('memory_limit', '512M');
            $app = $this->getSilexApplication();
            $timeStart = time();
            $msg = "Lancement de l'ajout des archives";
            $app['monolog.forex.archive']->addInfo($msg);
            $output->writeln($msg);
            $memoryFree = $app['service.system']->freeMemory($app['parameter.command_free_memory']);
            if ($memoryFree instanceof \Exception) {
                throw new \Exception($memoryFree->getMessage());
            }
            $phpMemoryUsage = $app['service.utils']->getPhpMemoryUsage();
            if ($phpMemoryUsage instanceof \Exception) {
                throw new \Exception($phpMemoryUsage->getMessage());
            }
            $output->writeln("Mémoire utilisée avant ajout des archives : " . $phpMemoryUsage);
            $exec = $this->launch($output);
            if ($exec instanceof \Exception) {
                throw new \Exception($exec->getMessage());
            }
            $timeElapsed = time() - $timeStart;
            $memoryFree = $app['service.system']->freeMemory($app['parameter.command_free_memory']);
            if ($memoryFree instanceof \Exception) {
                throw new \Exception($memoryFree->getMessage());
            }
            $phpMemoryUsage = $app['service.utils']->getPhpMemoryUsage();
            if ($phpMemoryUsage instanceof \Exception) {
                throw new \Exception($phpMemoryUsage->getMessage());
            }
            $output->writeln("Mémoire utilisée après ajout des archives : " . $phpMemoryUsage);
            $msg = "Operation effectuee en " . $timeElapsed . " secondes.";
            $app['monolog.forex.archive']->addInfo($msg);
            $output->writeln($msg);
        } catch (\Exception $ex) {
            $app['monolog.forex.archive']->addError($ex->getMessage());
            $output->writeln("Une erreur s'est produite : " . $ex->getMessage());
        }
    }

    private function launch($output) {
        try {
            $app = $this->getSilexApplication();
            $checkDirectory = $app['service.utils']->checkDirectory(
                $app['parameter.path.temp.forex']);
            if ($checkDirectory instanceof \Exception) {
                throw new \Exception($checkDirectory->getMessage());
            }
            $files = scandir($app['parameter.path.in.forex']);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $memoryFree = $app['service.system']->freeMemory(
                        $app['parameter.command_free_memory']);
                    if ($memoryFree instanceof \Exception) {
                        throw new \Exception($memoryFree->getMessage());
                    }
                    $output->writeln(
                        "Traitement du fichier " . $app['parameter.path.in.forex'] .
                             DIRECTORY_SEPARATOR . $file . "...");
                    $unzip = $app['service.utils']->unzip(
                        $app['parameter.path.in.forex'] . DIRECTORY_SEPARATOR . $file);
                    if ($unzip instanceof \Exception) {
                        throw new \Exception($unzip->getMessage());
                    }
                    $prepareFile = $this->prepareFile(
                        $app['parameter.path.in.forex'] . DIRECTORY_SEPARATOR .
                             str_replace('.zip', '.csv', $file));
                    if ($prepareFile instanceof \Exception) {
                        throw new \Exception($prepareFile->getMessage());
                    }
                    list ($table, $year, $month) = explode("-", $file);
                    $persistFile = $this->persistFile($table);
                    if ($persistFile instanceof \Exception) {
                        throw new \Exception($persistFile->getMessage());
                    }
                    copy($app['parameter.path.in.forex'] . DIRECTORY_SEPARATOR . $file, 
                        $app['parameter.path.end.forex'] . DIRECTORY_SEPARATOR . $file);
                    unlink($app['parameter.path.in.forex'] . DIRECTORY_SEPARATOR . $file);
                    unlink(
                        $app['parameter.path.in.forex'] . DIRECTORY_SEPARATOR .
                             str_replace('.zip', '.csv', $file));
                    $phpMemoryUsage = $app['service.utils']->getPhpMemoryUsage();
                    if ($phpMemoryUsage instanceof \Exception) {
                        throw new \Exception($phpMemoryUsage->getMessage());
                    }
                    $output->writeln("... Mémoire utilisée : " . $phpMemoryUsage);
                }
                gc_collect_cycles();
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function prepareFile($file) {
        try {
            $app = $this->getSilexApplication();
            $app['monolog.forex.archive']->addInfo("Préparation du fichier " . $file . " en cours...");
            if (($handleFile = fopen($file, "r")) !== FALSE) {
                $countFile = 0;
                $tempFile = fopen(
                    $app['parameter.path.temp.forex'] . DIRECTORY_SEPARATOR . '000_' .
                         basename($file), 'w');
                $countRow = 0;
                while (($row = fgetcsv($handleFile, 60, ",")) !== FALSE) {
                    if ($countRow > 50000) {
                        fclose($tempFile);
                        $countFile ++;
                        if ($countFile > 99) {
                            $index = $countFile;
                        } elseif ($countFile > 9) {
                            $index = '0' . $countFile;
                        } else {
                            $index = '00' . $countFile;
                        }
                        $tempFile = fopen(
                            $app['parameter.path.temp.forex'] . DIRECTORY_SEPARATOR . $index . '_' .
                                 basename($file), 'w');
                        $countRow = 0;
                        gc_collect_cycles();
                    }
                    fputcsv($tempFile, $row);
                    $countRow ++;
                }
                fclose($tempFile);
                fclose($handleFile);
                gc_collect_cycles();
            }
            $app['monolog.forex.archive']->addInfo(
                "Préparation du fichier " . $file . " effectué avec succès...");
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    private function persistFile($table) {
        try {
            $app = $this->getSilexApplication();
            $app['monolog.forex.archive']->addInfo("Persistance en cours...");
            $files = scandir($app['parameter.path.temp.forex']);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $app['monolog.forex.archive']->addInfo(
                        "..." . $app['parameter.path.temp.forex'] . DIRECTORY_SEPARATOR . $file .
                             "...");
                    $data = $app['service.forex']->insertCurrencyArchive($table, 
                        $app['parameter.path.temp.forex'] . DIRECTORY_SEPARATOR . $file);
                    if ($data instanceof \Exception) {
                        throw new \Exception($data->getMessage());
                    }
                    unlink($app['parameter.path.temp.forex'] . DIRECTORY_SEPARATOR . $file);
                    $app['monolog.forex.archive']->addInfo("...OK...");
                }
                gc_collect_cycles();
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}