<?php

namespace Simpnas\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Simpnas\User;
use Simpnas\Utils;
use Slim\Views\Twig;

class Login
{

    public function page(Request $request, Response $response, Twig $twig): Response {
        return $twig->render($response, 'login.twig', [
            'bodyClass' => ['text-center'],
            'showMenu' => false
        ]);
    }

    public function check(Request $request, Response $response, User $user): Response {
        $data = $request->getParsedBody();

        return Utils::redirect(
            $request,
            $response,
            $user->login($data['username'], $data['password']) ? 'dashboard' : 'login',
            303
        );
    }

}
