<?php

namespace Simpnas\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Simpnas\Utils\Disk;
use Simpnas\Utils\Volume;
use Slim\Views\Twig;

class Storage
{

    public function __construct(private Twig $twig)
    {
    }

    public function disks(Response $response): Response {
        return $this->twig->render($response, 'account/disks.twig', [
            'title' => ['Disks'],
            'disksList' => [Disk::getOsDisk(), ...Disk::getStorageDisks()]
        ]);
    }

    public function volumes(Response $response): Response {
        return $this->twig->render($response, 'account/volumes/index.twig', [
            'title' => ['Volumes'],
            'volumeList' => Volume::getList()
        ]);
    }

    public function createSimpleVolume(Response $response): Response {
        return $this->twig->render($response, 'account/volumes/createSimple.twig', [
            'title' => ['Volumes', 'Create Simple'],
            'diskOptions' => Disk::getStorageDisks(true)
        ]);
    }

    public function createRaidVolume(Response $response): Response {
        return $this->twig->render($response, 'account/volumes/createRaid.twig', [
            'title' => ['Volumes', 'Create Raid'],
            'diskOptions' => Disk::getStorageDisks(true)
        ]);
    }

}
