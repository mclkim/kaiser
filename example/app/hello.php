<?php

use Mcl\Kaiser\Controller;


/**
 * http://localhost/hello.world?p1=1&p2=2&p3=3
 */
class hello extends Controller
{
    function world($request, $response)
    {
        $getParams = $request->get();
        var_dump($getParams);
        echo '<br>';
        echo 'hello world~~~';
    }

    function index($request, $response)
    {
        return $response->status(200)->setContent('OK Kaiser Framework');
    }

    function not($request, $response)
    {
        return $response->status(404)->setContent('Not Found');
    }
}