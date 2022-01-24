<?php

namespace Simpnas\Controllers\Actions;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Simpnas\Utils\Functions;


class Settings
{

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function timezone(Request $request, Response $response): Response {
        $data = $request->getParsedBody();

        if (!Functions::isFakeEnabled()) {
            exec("timedatectl set-timezone " . escapeshellarg($data['timezone']));
        }

        return Functions::redirect(
            $request,
            $response,
            'account.dateTime',
            303
        );
    }

}
