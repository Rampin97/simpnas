<?php

namespace Simpnas\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Simpnas\User;
use Simpnas\Utils;

class Dashboard
{

    public function index(Request $request, Response $response, User $user): Response {
        return Utils::redirect(
            $request,
            $response,
            $user->isLoggedIn() ? 'dashboard' : 'login',
            302
        );
    }

    public function dashboard(Request $request, Response $response): Response {
        return $response;
    }

}
