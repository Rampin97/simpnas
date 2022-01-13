<?php

namespace Simpnas\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class Setup
{

    private Twig $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function step1(Request $request, Response $response): Response {
        return $this->twig->render($response, 'login.twig', [
            'bodyClass' => ['text-center'],
            'showMenu' => false
        ]);
    }

}
