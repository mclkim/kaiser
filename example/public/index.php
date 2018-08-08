<?php

require_once '../vendor/autoload.php';

use Mcl\Kaiser\RequestInterface as Request;
use Mcl\Kaiser\ResponseInterface as Response;

$app = new Mcl\Kaiser\App();

//// Define app routes
$app->addRoute('GET', '/hello/{name}', function ($request, $response, $args) {
    $response->setContent("Hello " . $args['name']);
});

$app->run('../app');
