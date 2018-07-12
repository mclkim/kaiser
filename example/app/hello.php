<?php

use Mcl\Kaiser\Controller;

/**
 * http://localhost/hello.world?p1=1&p2=2&p3=3
 */
class hello extends Controller
{
    function world(Request $request, Response $response)
    {
        $queryParams=$request->getQueryParam();
        var_dump($queryParams);

        echo '<br>';
        echo 'hello world~~~';

 return $response->withStatus(200)->write("Hello Kaiser PHP framework~~");
    }
}