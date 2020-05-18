<?php

use Mcl\Kaiser\Middleware\AuthMiddleware;
use Mcl\Kaiser\Middleware\RoutingMiddleware;
use Mcl\Kaiser\Middleware\SessionMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});

//TODO::mclkim
$route = new RoutingMiddleware($app->getContainer(), __DIR__ . '/../app');
if ($route = $route()) {
    $app->map($route['methods'], $route['pattern'], $route['handler'])
        ->add(new SessionMiddleware())
        ->add(new AuthMiddleware($route['callable']));
}

$app->run();