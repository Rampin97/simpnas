<?php

namespace Simpnas\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Simpnas\SimpleConst;
use Simpnas\Utils;
use Slim\Views\Twig;

class Setup
{

    private Twig $twig;

    private const defaultData = [
        'showMenu' => false,
        'showUser' => false
    ];

    private function getDiskOptions(): array
    {
        $osDisk = exec("lsblk -n -o pkname,MOUNTPOINT | grep -w / | awk '{print $1}'");
        exec("lsblk -n -o KNAME,TYPE | grep disk | grep -v zram | grep -v $osDisk | awk '{print $1}'", $disksList);

        $diskOptions = [];

        foreach ($disksList as $disk) {
            $diskVendor = exec("smartctl -i /dev/$disk | grep 'Model Family:' | awk '{print $3,$4,$5}'");

            if(empty($diskVendor)) {
                $diskVendor = exec("smartctl -i /dev/$disk | grep 'Device Model:' | awk '{print $3,$4,$5}'");
            }

            if(empty($diskVendor)) {
                $diskVendor = exec("smartctl -i /dev/$disk | grep 'Model Number:' | awk '{print $3,$4,$5,$6}'");
            }

            if(empty($diskVendor)) {
                $diskVendor = exec("lsblk -n -o kname,type,vendor /dev/$disk | grep disk  | awk '{print $3}'");
            }

            if(empty($diskVendor)) {
                $diskVendor = exec("lsblk -n -o kname,type,model /dev/$disk | grep disk  | awk '{print $3}'");
            }

            $diskSerial = exec("lsblk -n -o kname,type,serial /dev/$disk | grep disk  | awk '{print $3}'");
            $diskSize = exec("lsblk -n -o kname,type,size /dev/$disk | grep disk | awk '{print $3}'");

            $diskOptions[] = [
                'id' => $disk,
                'serial' => $diskSerial,
                'vendor' => $diskVendor,
                'size' => $diskSize
            ];
        }

        if (Utils::isFakeEnabled()) {
            $diskOptions = array_merge($diskOptions, [
                [
                    'id' => 'abc',
                    'serial' => 'cde',
                    'vendor' => 'efg',
                    'size' => 1000
                ],
                [
                    'id' => 'abc2',
                    'serial' => 'cde2',
                    'vendor' => 'efg2',
                    'size' => 2000
                ],
                [
                    'id' => 'abc3',
                    'serial' => 'cde3',
                    'vendor' => 'efg3',
                    'size' => 3000
                ]
            ]);
        }

        return $diskOptions;
    }

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
            'diskOptions' => $this->getDiskOptions(),
            'title' => ['Setup', 'Step 3', 'Simple']
        ]));
    }

    public function step3raid(Response $response): Response {
        return $this->twig->render($response, 'setup/setup3raid.twig', array_merge(self::defaultData, [
            'diskOptions' => $this->getDiskOptions(),
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
