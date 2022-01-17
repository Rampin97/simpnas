<?php

namespace Simpnas\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Simpnas\SimpleConst;
use Slim\Views\Twig;

class Power
{

    private Twig $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function shutdown(Response $response): Response {
        exec("(sleep 5 && halt -p) > /dev/null &");

        return $this->twig->render($response, 'power.twig', [
            'bodyClass' => SimpleConst::bodyCoverClass,
            'title' => ['Shutdown'],
            'powerType' => 'shutdown',
            'powerTitle' => 'Shutdown in progress...'
        ]);
    }

    public function reboot(Response $response): Response {
        exec("(sleep 5 && reboot) > /dev/null &");

        return $this->twig->render($response, 'power.twig', [
            'bodyClass' => SimpleConst::bodyCoverClass,
            'title' => ['Reboot'],
            'powerType' => 'reboot',
            'powerTitle' => 'Reboot in progress...'
        ]);
    }

}
