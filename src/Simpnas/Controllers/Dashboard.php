<?php

namespace Simpnas\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Simpnas\SimpleConst;
use Simpnas\SimpleVars;
use Simpnas\Utils\Disk;
use Simpnas\Utils\Functions;
use Simpnas\Utils\Volume;
use Slim\Views\Twig;

class Dashboard
{

    private Twig $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }


    public function dashboard(Response $response, SimpleVars $simpleVars): Response {
        exec("awk -F: '$3 > 999 {print $1}' /etc/passwd | grep -v nobody", $usersList);
        exec("awk -F: '$3 > 999 {print $1}' /etc/group | grep -v nogroup", $groupsList);
        exec("ls /etc/systemd/network", $networkList);
        exec("find /etc/cron.*/ -type f -name backup-* -printf '%f\n'", $backupJobsList);

        $freeMemory = floor((float) exec("free | grep Mem | awk '{print $3/$2 * 100.0}'"));
        $totalMemory = Functions::formatSize((float) exec("free -b | grep 'Mem:' | awk '{print $2}'"));

        $freeSwap = floor((float) exec("free | grep Swap | awk '{print $3/$2 * 100.0}'"));
        $totalSwap = Functions::formatSize((float) exec("free -b | grep 'Swap:' | awk '{print $2}'"));

        $currentLoad = exec("cat /proc/loadavg | awk '{print $1}'");

        $uptime = exec("uptime -p | cut -c 4-");
        $systemTime = exec("date");
        $machineId = exec("cat /etc/machine-id");
        $cpuModel = exec("lscpu | grep 'Model name:' | sed -r 's/Model name:\s{1,}//g'");
        $cpuCores = (int) exec("lscpu | grep 'CPU(s):' | awk '{print $2}'");
        $cpuSpeed = round((float) exec("lscpu | grep 'CPU max MHz:' | awk '{print $4}'"));
        $osName = exec("hostnamectl | grep 'Operating System:' | awk '{print $3, $4, $5, $6}'");
        $kernel = exec("hostnamectl | grep 'Kernel:' | awk '{print $3}'");


        $usersCount = count($usersList);
        $groupsCount = count($groupsList);
        $volumesCount = count(Volume::getVolumeList());
        $disksCount = count(Disk::getStorageDisks());
        $sharesCount = (int) exec("(ls /etc/samba/shares | wc -l) 2> /dev/null");
        $appsCount = ((int) exec("docker ps | wc -l")) - 1;
        $networkCount = count($networkList);
        $backupJobsCount = count($backupJobsList);

        $data = [
            'title' => ['Dashboard'],
            'services' => [
                'smbd' => $simpleVars->isServiceRunning('smbd'),
                'nmbd' => $simpleVars->isServiceRunning('nmbd'),
                'docker' => $simpleVars->isServiceRunning('docker'),
                'ssh' => $simpleVars->isServiceRunning('ssh')
            ],
            'count' => [
                'users' => $usersCount,
                'groups' => $groupsCount,
                'volumes' => $volumesCount,
                'disks' => $disksCount,
                'shares' => $sharesCount,
                'apps' => $appsCount,
                'network' => $networkCount,
                'backupJobs' => $backupJobsCount
            ],
            'cpu' => [
                'model' => $cpuModel,
                'cores' => $cpuCores,
                'speed' => $cpuSpeed,
            ],
            'memory' => [
                'free' => $freeMemory,
                'total' => $totalMemory
            ],
            'swap' => [
                'free' => $freeSwap,
                'total' => $totalSwap
            ],
            'load' => $currentLoad,
            'osName' => $osName,
            'kernel' => $kernel,
            'uptime' => $uptime,
            'systemTime' => $systemTime,
            'machineId' => $machineId,
            'simpNasVersion' => SimpleConst::simpNasVersion,
            'volumeList' => Volume::getVolumeList()
        ];

        return $this->twig->render($response, 'account/dashboard.twig', $data);
    }

}
