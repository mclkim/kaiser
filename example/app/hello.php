<?php

use Mcl\Kaiser\Controller;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

/**
 * http://localhost/hello.world?p1=1&p2=2&p3=3
 */
class hello extends Controller
{
    function world(ServerRequest $request, Response $response): Response
    {
        $getParams = $request->getParams();
        var_dump($getParams);
        echo '<br>';
        echo 'hello world~~~';
        return $response;
    }
}