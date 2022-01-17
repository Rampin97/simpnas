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

        $group->get('/step-3/simple', [Setup::class, 'step3simple'])
            ->setName('setup.step3.simple');

        $group->get('/step-3/raid', [Setup::class, 'step3raid'])
            ->setName('setup.step3.raid');

        $group->get('/step-4', [Setup::class, 'step4'])
            ->setName('setup.step4');

        $group->get('/complete', [Setup::class, 'complete'])
            ->setName('setup.complete');

    })->add(SetupTodo::class);
};
