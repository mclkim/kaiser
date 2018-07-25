<?php

namespace admin;

use Mcl\Kaiser\Controller;

/**
 * http://localhost/admin/hello.world?p1=1&p2=2&p3=3
 */
class hello extends Controller
{
    function requireAdmin()
    {
        return true;
    }

    function world($request, $response)
    {
        $getParams = $request->get();
        var_dump($getParams);
        echo '<br>';
        echo 'Admin! hello world~~~';
    }
}