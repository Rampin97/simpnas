<?php

namespace Simpnas\Routes;


use Simpnas\Controllers\Setup;
use Simpnas\Middleware\SetupTodo;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app) {
    $app->group('/setup', function (RouteCollectorProxy $group) {

        $group->get('/step-1', [Setup::class, 'step1'])
            ->setName('setup.step1');

    })->add(SetupTodo::class);
};
