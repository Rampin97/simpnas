<?php

namespace Simpnas\Routes;


use Simpnas\Controllers\Actions\Setup;
use Simpnas\Controllers\Login;
use Simpnas\Middleware\SetupTodo;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app) {
    $app->group('/actions', function (RouteCollectorProxy $group) {

        $group->post('/login', [Login::class, 'check'])
            ->setName('actions.login');

        $group->group('/setup', function (RouteCollectorProxy $group) {
            $group->post('/step1', [Setup::class, 'step1'])
                ->setName('actions.setup.step1');

            $group->post('/step2', [Setup::class, 'step2'])
                ->setName('actions.setup.step2');

            $group->post('/step3/simple', [Setup::class, 'step3simple'])
                ->setName('actions.setup.step3.simple');

            $group->post('/step3/raid', [Setup::class, 'step3raid'])
                ->setName('actions.setup.step3.raid');

            $group->post('/step4', [Setup::class, 'step4'])
                ->setName('actions.setup.step4');
        })->add(SetupTodo::class);

    });
};
