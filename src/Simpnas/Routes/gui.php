<?php

namespace Simpnas\Routes;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app) {
    $app->group('', function (RouteCollectorProxy $group) {

        $group->get('/cancel-orders', [Cron::class, 'cancelOrders']);

    })->add(SetupCompleted::class);
};
