<?php

namespace Simpnas\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Simpnas\User;
use Simpnas\Utils\Functions;
use Slim\App;

class UserLoggedIn implements MiddlewareInterface
{

    private App $app;

    private User $user;


    public function __construct(App $app, User $user)
    {
        $this->app = $app;
        $this->user = $user;
    }

    /**
     * @param Request $request
     * @param RequestHandlerInterface $handler
     * @return Response
     */
    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        if ($this->user->isLoggedIn()) {
            return $handler->handle($request);
        }

        return Functions::redirect(
            $request,
            $this->app->getResponseFactory()->createResponse(),
            'login',
            302
        );
    }

}
