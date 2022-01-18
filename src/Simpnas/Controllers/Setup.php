<?php

namespace Simpnas\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Simpnas\SimpleConst;
use Simpnas\Utils\Disk;
use Simpnas\Utils\Functions;
use Slim\Views\Twig;

class Setup
{

    private Twig $twig;

    private const defaultData = [
        'showMenu' => false,
        'showUser' => false
    ];

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function welcome(Response $response): Response {
        return $this->twig->render($response, 'setup/welcome.twig', array_merge(self::defaultData, [
            'bodyClass' => SimpleConst::bodyCoverClass,
            'title' => ['Setup', 'Welcome']
        ]));
    }

    public function step1(Response $response): Response {
        $currentTimezone = exec("timedatectl show -p Timezone --value");
        exec("timedatectl list-timezones", $timezoneList);

        return $this->twig->render($response, 'setup/setup1.twig', array_merge(self::defaultData, [
            'currentTimezone' => $currentTimezone,
            'timezoneList' => $timezoneList,
            'title' => ['Setup', 'Step 1']
        ]));
    }

    public function step2(Response $response): Response {
        exec("ls /sys/class/net | grep -v docker | grep -v lo | grep -v veth | grep -v br-", $networkList);

        return $this->twig->render($response, 'setup/setup2.twig', array_merge(self::defaultData, [
            'networkList' => $networkList,
            'title' => ['Setup', 'Step 2']
        ]));
    }

    public function step3simple(Response $response): Response {
        return $this->twig->render($response, 'setup/setup3simple.twig', array_merge(self::defaultData, [
            'diskOptions' => Disk::getStorageDisks(),
            'title' => ['Setup', 'Step 3', 'Simple']
        ]));
    }

    public function step3raid(Response $response): Response {
        return $this->twig->render($response, 'setup/setup3raid.twig', array_merge(self::defaultData, [
            'diskOptions' => Disk::getStorageDisks(),
            'title' => ['Setup', 'Step 3', 'RAID']
        ]));
    }

    public function step4(Response $response): Response {
        return $this->twig->render($response, 'setup/setup4.twig', array_merge(self::defaultData, [
            'title' => ['Setup', 'Step 4']
        ]));
    }

    public function complete(Response $response): Response {
        return $this->twig->render($response, 'setup/complete.twig', array_merge(self::defaultData, [
            'bodyClass' => SimpleConst::bodyCoverClass,
            'title' => ['Setup', 'Complete']
        ]));
    }

}
