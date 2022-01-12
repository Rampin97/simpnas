<?php

namespace Simpnas\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Setup
{

    public function step1(Request $request, Response $response): Response {
        return $response;
    }

}
