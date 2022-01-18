<?php

namespace Simpnas\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Simpnas\SimpleConst;
use Simpnas\User;
use Simpnas\Utils\Functions;

class Extra
{

    public function index(Request $request, Response $response, User $user): Response {
        return Functions::redirect(
            $request,
            $response,
            $user->isLoggedIn() ? 'dashboard' : 'login',
            302
        );
    }

    public function fileManager(Request $request, Response $response): Response {
        $uri = $request->getUri();
        $uri = $uri->withPort(SimpleConst::fileManagerPort)
            ->withFragment('')
            ->withPath('')
            ->withQuery('');

        return Functions::redirect(
            $request,
            $response,
            (string) $uri,
            302
        );
    }

}
