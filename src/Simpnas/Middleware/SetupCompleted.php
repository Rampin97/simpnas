<?php

namespace Simpnas\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Simpnas\SimpleVars;
use Simpnas\Utils\Functions;
use Slim\App;

class SetupCompleted implements MiddlewareInterface
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
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->simpleVars->getDatabaseKey(SimpleVars::DBKEY_SETUP, false)) {
            return $handler->handle($request);
        }

        return Functions::redirect(
            $request,
            $this->app->getResponseFactory()->createResponse(),
            'setup.welcome',
            302
        );
    }
}
