<?php

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');

require __DIR__ . '/../vendor/autoload.php';

session_start();

$builder = new ContainerBuilder();

$builder->addDefinitions([
    Twig::class => DI\factory(static function (ContainerInterface $container) {
        $twig = Twig::create(__DIR__ . '/../templates');

        $env = $twig->getEnvironment();

        $env->setCache(__DIR__ . '/../cache');
        $env->addExtension($container->get(SimpleVars::class));

        return $twig;
    }),
    SimpleVars::class => DI\factory(static function () {
        return new SimpleVars();
    })
]);

$builder->enableCompilation(__DIR__ . '/../cache');
// $builder->enableDefinitionCache();
$builder->writeProxiesToFile(true, __DIR__ . '/../cache');

try {
    $container = $builder->build();

    $app = AppFactory::createFromContainer($container);



    $routeCollector = $app->getRouteCollector();
    $routeCollector->setCacheFile(__DIR__ . '/../cache/routes.php');

    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();
    $app->add(TwigMiddleware::createFromContainer($app));
    $app->addErrorMiddleware(true, true, true);

    $app->run();
} catch (Exception $e) {
    echo $e->getMessage();
}
