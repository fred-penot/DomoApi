<?php
namespace DomoApi\Services;

class System
{

    public function __construct()
    {}

    public function __destruct()
    {}

    public function getInfoHdd($pathHdd)
    {
        try {
            $isHddConnected = $this->isDiskMounted($pathHdd);
            if ($isHddConnected) {
                $action = false;
            } else {
                $action = true;
            }
            $infoHdd = array(
                "isConnect" => $isHddConnected,
                "action" => $action
            );
            return $infoHdd;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getInfoService($paramStart)
    {
        try {
            $serviceRunning = $this->isServiceRunning($paramStart);
            if ($serviceRunning) {
                $action = "off";
            } else {
                $action = "on";
            }
            $infoService = array(
                "isRunning" => $serviceRunning,
                "action" => $action
            );
            return $infoService;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function execBooleanAction($action, $command, $command2 = '', $sleep = 0)
    {
        try {
            if ($action) {
                exec($command);
            } else {
                exec($command2);
            }
            if ($sleep > 0) {
                sleep($sleep);
            }
            return true;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    /**
     * Contrôle si un disque est monté au point
     * de montage fourni ou non
     *
     * @param string $pathMountPoint            
     * @return boolean
     */
    public function isDiskMounted($pathMountPoint)
    {
        try {
            $command = 'df | grep ' . $pathMountPoint . ' | wc -l';
            $output = array();
            exec($command, $output);
            $diskMounted = false;
            if ($output[0] == "1") {
                $diskMounted = true;
            }
            return $diskMounted;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    /**
     * Contrôle si le service est démarré ou non
     * en fonction de la commande donnée
     *
     * @param string $serviceCommand            
     * @return boolean
     */
    public function isServiceRunning($serviceCommand)
    {
        try {
            $command = 'ps -ef | grep "' . $serviceCommand . '" | grep -v grep | wc -l';
            $output = array();
            exec($command, $output);
            $serviceRunning = false;
            if ($output[0] == "1") {
                $serviceRunning = true;
            }
            return $serviceRunning;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getDataUsageRam()
    {
        try {
            $output = array();
            exec('free -h | grep Mem', $output);
            for ($f = 2; $f < 5; $f ++) {
                exec("echo " . $output[0] . "  | cut -d' ' -f$f", $output);
            }
            $ram = array(
                "total" => $output[1],
                "used" => $output[2],
                "free" => $output[3]
            );
            return $ram;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function getDataUsageDisk($hddMountPoint)
    {
        try {
            $disk = array();
            $mountPointHdds = explode("|", $hddMountPoint);
            foreach ($mountPointHdds as $mountPointHdd) {
                list ($diskName, $mountPoint) = explode(";", $mountPointHdd);
                $output = array();
                exec('df -h | grep "' . $mountPoint . '"', $output);
                $line = str_replace($mountPoint, '', $output[0]);
                for ($f = 1; $f < 5; $f ++) {
                    exec("echo " . $line . "  | cut -d' ' -f$f", $output);
                }
                $disk[] = array(
                    "mountPoint" => array(
                        "name" => $diskName,
                        "path" => $mountPoint
                    ),
                    "info" => array(
                        "totalSize" => $output[1],
                        "sizeUsed" => $output[2],
                        "sizeFree" => $output[3],
                        "sizeUsedPourcent" => $output[4]
                    )
                );
            }
            return $disk;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function freeMemory($command)
    {
        try {
            $memoryBefore = array();
            exec('free | grep Mem', $memoryBefore);
            exec("echo " . $memoryBefore[0] . "  | cut -d' ' -f4", $memoryBefore);
            exec($command);
            $memoryAfter = array();
            exec('free | grep Mem', $memoryAfter);
            exec("echo " . $memoryAfter[0] . "  | cut -d' ' -f4", $memoryAfter);
            $memoryFreeOctet = intval($memoryAfter[1]) - intval($memoryBefore[1]);
            if ($memoryFreeOctet < 1000000) {
                $memoryFree = intval(($memoryFreeOctet / 1024)) . "Mo";
            } else {
                $memoryFree = intval(($memoryFreeOctet / (1024 * 1024))) . "Go";
            }
            return $memoryFree;
        } catch (\Exception $ex) {
            return $ex;
        }
    }
}