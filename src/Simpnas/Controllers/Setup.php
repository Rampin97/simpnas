<?php

namespace Simpnas\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Simpnas\SimpleVars;
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
            'bodyClass' => ['d-flex', 'h-100', 'text-center', 'text-white', 'bg-dark']
        ]));
    }

    public function step1(Response $response): Response {
        $currentTimezone = exec("timedatectl show -p Timezone --value");
        exec("timedatectl list-timezones", $timezoneList);

        return $this->twig->render($response, 'setup/setup1.twig', array_merge(self::defaultData, [
            'currentTimezone' => $currentTimezone,
            'timezoneList' => $timezoneList
        ]));
    }

    public function step2(Response $response): Response {
        $currentTimezone = exec("timedatectl show -p Timezone --value");
        exec("timedatectl list-timezones", $timezoneList);

        return $this->twig->render($response, 'setup/setup1.twig', array_merge(self::defaultData, [
            'currentTimezone' => $currentTimezone,
            'timezoneList' => $timezoneList
        ]));
    }

}
