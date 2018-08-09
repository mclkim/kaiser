<?php

require_once '../vendor/autoload.php';

use Mcl\Kaiser\RequestInterface as Request;
use Mcl\Kaiser\ResponseInterface as Response;

$app = new Mcl\Kaiser\App();

//// Define app routes
$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello " . $args['name']);
    return $response;
});

$app->run('../app');
