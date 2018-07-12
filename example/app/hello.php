<?php

use Mcl\Kaiser\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * http://localhost/hello.world?p1=1&p2=2&p3=3
 */
class hello extends Controller
{
    function world(Request $request, Response $response)
    {
        $getParams = $request->getQueryParams();
        var_dump($getParams);

        echo '<br>';
        echo 'hello world~~~';
    }
}