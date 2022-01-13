<?php

use DI\Bridge\Slim\Bridge;
use DI\ContainerBuilder;
use Simpnas\SimpleVars;
use Simpnas\User;
use Simpnas\Utils;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Extension\SlimFlashMessages;

require __DIR__ . '/../../vendor/autoload.php';

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

session_start();

$builder = new ContainerBuilder();

$builder->addDefinitions([
    Twig::class => DI\factory(static function (SimpleVars $simpleVars, Messages $messages, User $user) {
        $twig = Twig::create(__DIR__ . '/../templates', [
            'cache' => Utils::isCacheEnabled() ? Utils::cacheFolder : false
        ]);

        $twig->addExtension($simpleVars);
        $twig->addExtension($user);
        $twig->addExtension(new SlimFlashMessages($messages));

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

if (Utils::isCacheEnabled()) {
    $builder->enableCompilation(Utils::cacheFolder);
    // $builder->enableDefinitionCache();
    $builder->writeProxiesToFile(true, Utils::cacheFolder);
}

try {
    $app = Bridge::create($builder->build());

    (require(__DIR__ . '/../Simpnas/Routes/gui.php'))($app);
    (require(__DIR__ . '/../Simpnas/Routes/setup.php'))($app);
    (require(__DIR__ . '/../Simpnas/Routes/actions.php'))($app);

    if (Utils::isCacheEnabled()) {
        $routeCollector = $app->getRouteCollector();
        $routeCollector->setCacheFile(Utils::cacheFolder . "/routes.php");
    }

    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();
    $app->add(TwigMiddleware::createFromContainer($app, Twig::class));
    $app->addErrorMiddleware(true, true, true);

    $app->run();
} catch (Exception $e) {
    echo $e->getMessage();
}
