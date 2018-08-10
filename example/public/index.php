<?php

require_once '../vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new Mcl\Kaiser\App(['settings' => ['displayErrorDetails' => true]]);

//// Define app routes
$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello " . $args['name']);
    return $response;
});

$app->run('../app');
