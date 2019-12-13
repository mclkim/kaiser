<?php

namespace admin;

use Mcl\Kaiser\Controller;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

/**
 * http://localhost/admin/hello.world?p1=1&p2=2&p3=3
 */
class hello extends Controller
{
    function requireAdmin()
    {
        return true;
    }

    function world(ServerRequest $request, Response $response): Response
    {
        $getParams = $request->getParams();
        var_dump($getParams);
        echo '<br>';
        echo 'Admin! hello world~~~';
        return $response;
    }
}