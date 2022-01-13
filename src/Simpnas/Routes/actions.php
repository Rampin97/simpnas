<?php

namespace Simpnas\Routes;


use Simpnas\Controllers\Login;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app) {
    $app->group('/actions', function (RouteCollectorProxy $group) {

        $group->post('/login', [Login::class, 'check'])
            ->setName('actions.login');

    });
};
