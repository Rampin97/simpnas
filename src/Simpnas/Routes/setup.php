<?php

namespace Simpnas\Routes;


use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app) {
    $app->group('/setup', function (RouteCollectorProxy $group) {

        $group->get('/step-1', [Cron::class, 'cancelOrders']);

    })->addMiddleware(new CheckValidCron());
};
