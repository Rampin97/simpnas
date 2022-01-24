<?php

use DI\Bridge\Slim\Bridge;
use DI\ContainerBuilder;
use Simpnas\SimpleVars;
use Simpnas\User;
use Simpnas\Utils\Functions;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Extension\DebugExtension;
use Twig\Extension\SlimFlashMessages;
use function Simpnas\Routes\actions;
use function Simpnas\Routes\gui;
use function Simpnas\Routes\setup;

require __DIR__ . '/../../vendor/autoload.php';

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

session_start();

$builder = new ContainerBuilder();

$builder->addDefinitions([
    Twig::class => DI\factory(static function (SimpleVars $simpleVars, Messages $messages, User $user) {
        $cache = Functions::isCacheEnabled();

        $twig = Twig::create(__DIR__ . '/../templates', [
            'debug' => !$cache,
            'cache' => $cache ? Functions::cacheFolder : false
        ]);

        $twig->addExtension($simpleVars);
        $twig->addExtension($user);
        $twig->addExtension(new SlimFlashMessages($messages));

        if (!$cache) {
            $twig->addExtension(new DebugExtension());
        }

        return $twig;
    }),
    SimpleVars::class => DI\factory(static function () {
        return new SimpleVars();
    }),
    User::class => DI\factory(static function (Messages $messages) {
        return new User($messages);
    }),
    Messages::class => DI\factory(static function () {
        return new Messages($_SESSION);
    })
]);

if (Functions::isCacheEnabled()) {
    $builder->enableCompilation(Functions::cacheFolder);
    // $builder->enableDefinitionCache();
    $builder->writeProxiesToFile(true, Functions::cacheFolder);
}

try {
    $app = Bridge::create($builder->build());

    actions($app);
    setup($app);
    gui($app);

    if (Functions::isCacheEnabled()) {
        $routeCollector = $app->getRouteCollector();
        $routeCollector->setCacheFile(Functions::cacheFolder . "/routes.php");
    }

    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();
    $app->add(TwigMiddleware::createFromContainer($app, Twig::class));
    $app->addErrorMiddleware(true, true, true);

    $app->run();
} catch (Exception $e) {
    echo $e->getMessage();
}
