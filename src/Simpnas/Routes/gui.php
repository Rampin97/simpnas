<?php

namespace Simpnas\Routes;

use Simpnas\Controllers\Dashboard;
use Simpnas\Middleware\SetupCompleted;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app) {
    $app->group('', function (RouteCollectorProxy $group) {

        $group->get('/', [Dashboard::class, 'index'])
            ->setName('index');

        $group->get('/dashboard', [Dashboard::class, 'dashboard'])
            ->setName('dashboard');

    })->add(SetupCompleted::class);
};
