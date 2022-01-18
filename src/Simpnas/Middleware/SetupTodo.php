<?php

namespace Simpnas\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Simpnas\SimpleVars;
use Simpnas\Utils\Functions;
use Slim\App;

class SetupTodo implements MiddlewareInterface
{

    private App $app;

    private SimpleVars $simpleVars;

    public function __construct(App $app, SimpleVars $simpleVars)
    {
        $this->app = $app;
        $this->simpleVars = $simpleVars;
    }

    /**
     * @inheritDoc
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        if ($this->simpleVars->getDatabaseKey(SimpleVars::DBKEY_SETUP, false)) {
            return Functions::redirect(
                $request,
                $this->app->getResponseFactory()->createResponse(),
                'account.dashboard',
                302
            );
        }

        return $handler->handle($request);
    }
}
