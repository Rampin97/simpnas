<?php

namespace Simpnas\Routes;


use Simpnas\Controllers\Setup;
use Simpnas\Middleware\SetupTodo;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app) {
    $app->group('/setup', function (RouteCollectorProxy $group) {

        $group->get('/welcome', [Setup::class, 'welcome'])
            ->setName('setup.welcome');

        $group->get('/step-1', [Setup::class, 'step1'])
            ->setName('setup.step1');

        $group->get('/step-2', [Setup::class, 'step2'])
            ->setName('setup.step2');

    })->add(SetupTodo::class);
};
