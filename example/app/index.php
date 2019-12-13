<?php

use Mcl\Kaiser\Controller;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

/**
 * http://localhost/
 */
class index extends Controller
{
    function execute(ServerRequest $request, Response $response): Response
    {
        echo 'Kaiser PHP framework~~';
        return $response;
    }
}