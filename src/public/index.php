<?php

use DI\Bridge\Slim\Bridge;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Simpnas\SimpleVars;
use Simpnas\Utils;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

require __DIR__ . '/../../vendor/autoload.php';

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

session_start();

$builder = new ContainerBuilder();

$builder->addDefinitions([
    Twig::class => DI\factory(static function (ContainerInterface $container) {
        $twig = Twig::create(__DIR__ . '/../templates');

        $env = $twig->getEnvironment();

        if (Utils::isCacheEnabled()) {
            $env->setCache(Utils::cacheFolder);
        }

        $env->addExtension($container->get(SimpleVars::class));

        return $twig;
    }),
    SimpleVars::class => DI\factory(static function () {
        return new SimpleVars();
    })
]);

if (Utils::isCacheEnabled()) {
    $builder->enableCompilation(Utils::cacheFolder);
    // $builder->enableDefinitionCache();
    $builder->writeProxiesToFile(true, Utils::cacheFolder);
}

try {
    $container = $builder->build();

    $app = Bridge::create($container);

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
