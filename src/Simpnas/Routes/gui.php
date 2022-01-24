<?php

namespace Simpnas\Routes;

use Simpnas\Controllers\Dashboard;
use Simpnas\Controllers\Extra;
use Simpnas\Controllers\Login;
use Simpnas\Controllers\Power;
use Simpnas\Controllers\Settings;
use Simpnas\Controllers\Shares;
use Simpnas\Controllers\Storage;
use Simpnas\Middleware\SetupCompleted;
use Simpnas\Middleware\UserLoggedIn;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function(App $app) {
    $app->group('', function (RouteCollectorProxy $group) {

        $group->get('/', [Extra::class, 'index'])
            ->setName('index');

        $group->get('/file-manager', [Extra::class, 'fileManager'])
            ->setName('fileManager');

        $group->get('/login', [Login::class, 'page'])
            ->setName('login');

        $group->group('/power', function (RouteCollectorProxy $group) {

            $group->get('/shutdown', [Power::class, 'shutdown'])
                ->setName('power.shutdown');

            $group->get('/reboot', [Power::class, 'reboot'])
                ->setName('power.reboot');

        });

        $group->group('/account', function (RouteCollectorProxy $group) {

            $group->get('/dashboard', [Dashboard::class, 'dashboard'])
                ->setName('account.dashboard');

            $group->group('/storage', function (RouteCollectorProxy $group) {
                $group->get('/disks', [Storage::class, 'disks'])
                    ->setName('account.disks');

                $group->group('/volumes', function (RouteCollectorProxy $group) {
                    $group->get('', [Storage::class, 'volumes'])
                        ->setName('account.volumes');

                    $group->get('/create/simple', [Storage::class, 'createSimpleVolume'])
                        ->setName('account.volumes.add.simple');
                    $group->get('/create/raid', [Storage::class, 'createRaidVolume'])
                        ->setName('account.volumes.add.raid');
                });
            });

            $group->group('/settings', function (RouteCollectorProxy $group) {
                $group->get('/date-and-time', [Settings::class, 'dateTime'])
                    ->setName('account.dateTime');

                $group->get('/network', [Settings::class, 'network'])
                    ->setName('account.network');
            });

            $group->group('/users-and-share', function (RouteCollectorProxy $group) {

                $group->group('/users', function (RouteCollectorProxy $group) {
                    $group->get('', [Shares::class, 'users'])
                        ->setName('account.users');

                    $group->get('/create', [Shares::class, 'createUser'])
                        ->setName('account.users.create');

                    $group->get('/edit/{username}', [Shares::class, 'editUser'])
                        ->setName('account.users.edit');
                });

                $group->group('/groups', function (RouteCollectorProxy $group) {
                    $group->get('', [Shares::class, 'groups'])
                        ->setName('account.groups');

                    $group->get('/create', [Shares::class, 'createGroup'])
                        ->setName('account.groups.create');

                    $group->get('/edit/{name}', [Shares::class, 'editGroup'])
                        ->setName('account.groups.edit');
                });

                $group->group('/shares', function (RouteCollectorProxy $group) {
                    $group->get('', [Shares::class, 'shares'])
                        ->setName('account.shares');

                    $group->get('/create', [Shares::class, 'createShare'])
                        ->setName('account.shares.create');

                    $group->get('/edit/{name}', [Shares::class, 'editShare'])
                        ->setName('account.shares.edit');
                });

            });

        })->add(UserLoggedIn::class);

    })->add(SetupCompleted::class);
};
