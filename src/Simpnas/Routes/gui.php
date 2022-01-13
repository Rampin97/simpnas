<?php

namespace Simpnas\Routes;

use Simpnas\Controllers\Dashboard;
use Simpnas\Controllers\Extra;
use Simpnas\Controllers\Login;
use Simpnas\Middleware\SetupCompleted;
use Simpnas\Middleware\UserLoggedIn;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app) {
    $app->group('', function (RouteCollectorProxy $group) {

        $group->get('/', [Extra::class, 'index'])
            ->setName('index');

        $group->get('/file-manager', [Extra::class, 'fileManager'])
            ->setName('fileManager');

        $group->get('/login', [Login::class, 'page'])
            ->setName('login');

        $group->group('/account', function (RouteCollectorProxy $group) {

            $group->get('/dashboard', [Dashboard::class, 'dashboard'])
                ->setName('dashboard');

        })->add(UserLoggedIn::class);

    })->add(SetupCompleted::class);
};
