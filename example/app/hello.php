<?php

//namespace App;

use Mcl\Kaiser\Controller;

/**
 * http://localhost/hello.world?p1=1&p2=2&p3=3
 */
class hello extends Controller
{
    function world($request, $response)
    {
        $getParams = $request->getParams();
        var_dump($getParams);
        echo '<br>';
        echo 'hello world~~~';
    }
}