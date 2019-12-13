<?php

use DI\ContainerBuilder;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();

// configure PHP-DI here
$containerBuilder->addDefinitions([
    'settings' => [
        'displayErrorDetails' => true, // Should be set to false in production
    ],
]);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$container = $app->getContainer();

//TODO::KAR-START
$request = ServerRequestFactory::createFromGlobals();
$path = $request->getUri()->getPath();

$router = new \Mcl\Kaiser\Router($path);
$router->setAppMap(__DIR__ . '../app');
$routeInfo = $router->dispatch();

if (is_array($routeInfo) && $routeInfo[0] == \Mcl\Kaiser\Router::FOUND) {
    $callable = new $routeInfo[1] ($container);
    if ($callable instanceof \Mcl\Kaiser\ControllerInterface) {
        $app->map($callable->methods(), $path, [$callable, $routeInfo[2]])->add(new \Mcl\Kaiser\Auth($callable));
    }
}
//TODO::KAR-END

$app->run();