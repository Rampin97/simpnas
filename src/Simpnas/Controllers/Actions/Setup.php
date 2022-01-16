<?php

namespace Simpnas\Controllers\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Simpnas\Utils;


class Setup
{

    public function step1(Request $request, Response $response): Response {

        $data = $request->getParsedBody();

        if (!Utils::isFakeEnabled()) {
            exec(sprintf('timedatectl set-timezone %s', escapeshellarg($data['timezone'])));
        }

        return Utils::redirect(
            $request,
            $response,
            'setup.step2',
            303
        );
    }

}
