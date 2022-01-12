<?php

use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->group('/setup', function (RouteCollectorProxy $group) {

        $group->get('/cancel-orders', [Cron::class, 'cancelOrders']);

    })->addMiddleware(new CheckValidCron());
};
