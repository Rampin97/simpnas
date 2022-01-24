<?php

namespace Simpnas\Routes;


use Simpnas\Controllers\Actions\Group;
use Simpnas\Controllers\Actions\Settings;
use Simpnas\Controllers\Actions\Setup;
use Simpnas\Controllers\Actions\Share;
use Simpnas\Controllers\Actions\User;
use Simpnas\Controllers\Actions\Volume;
use Simpnas\Controllers\Login;
use Simpnas\Middleware\SetupCompleted;
use Simpnas\Middleware\SetupTodo;
use Simpnas\Middleware\UserLoggedIn;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

function actions(App $app) {
    $app->group('/actions', function (RouteCollectorProxy $group) {

        $group->group('', function (RouteCollectorProxy $group) {
            $group->group('', function (RouteCollectorProxy $group) {

                $group->group('/settings', function (RouteCollectorProxy $group) {
                    $group->post('/timezone', [Settings::class, 'timezone'])
                        ->setName('actions.settings.timezone');
                });

                $group->group('/volumes', function (RouteCollectorProxy $group) {
                    $group->post('/delete', [Volume::class, 'delete'])
                        ->setName('actions.volumes.delete');

                    $group->post('/unlock', [Volume::class, 'unlock'])
                        ->setName('actions.volumes.unlock');

                    $group->post('/add/simple', [Volume::class, 'addSimple'])
                        ->setName('actions.volumes.add.simple');

                    $group->post('/add/raid', [Volume::class, 'addRaid'])
                        ->setName('actions.volumes.add.raid');
                });

                $group->group('/users', function (RouteCollectorProxy $group) {
                    $group->post('/delete', [User::class, 'delete'])
                        ->setName('actions.users.delete');

                    $group->post('/edit', [User::class, 'edit'])
                        ->setName('actions.users.edit');

                    $group->post('/add', [User::class, 'add'])
                        ->setName('actions.users.add');

                    $group->post('/disabled', [User::class, 'disabled'])
                        ->setName('actions.users.disabled');
                });

                $group->group('/groups', function (RouteCollectorProxy $group) {
                    $group->post('/delete', [Group::class, 'delete'])
                        ->setName('actions.groups.delete');

                    $group->post('/edit', [Group::class, 'edit'])
                        ->setName('actions.groups.edit');

                    $group->post('/add', [Group::class, 'add'])
                        ->setName('actions.groups.add');
                });

                $group->group('/shares', function (RouteCollectorProxy $group) {
                    $group->post('/delete', [Share::class, 'delete'])
                        ->setName('actions.shares.delete');

                    $group->post('/edit', [Share::class, 'edit'])
                        ->setName('actions.shares.edit');

                    $group->post('/add', [Share::class, 'add'])
                        ->setName('actions.shares.add');
                });

            })->add(UserLoggedIn::class);

            $group->post('/login', [Login::class, 'check'])
                ->setName('actions.login');

        })->add(SetupCompleted::class);

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
