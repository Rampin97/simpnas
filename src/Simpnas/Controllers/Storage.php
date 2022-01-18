<?php

namespace Simpnas\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Simpnas\Utils\Disk;
use Slim\Views\Twig;

class Storage
{

    private Twig $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function disks(Response $response): Response {
        return $this->twig->render($response, 'account/disks.twig', [
            'title' => ['Disks'],
            'disksList' => [Disk::getOsDisk(), ...Disk::getStorageDisks()]
        ]);
    }

}
