<?php

use Mcl\Kaiser\Controller;

/**
 * http://localhost/?hello.world&p1=1&p2=2&p3=3
 */
class hello extends Controller
{
    function requireLogin()
    {
        return false;
    }

    function world($param1 = null, $param2 = null, $param3 = null)
    {
        var_dump($param1);
        var_dump($param2);
        var_dump($param3);

        echo '<br>';
        echo 'hello world~~~';
        return $param1 + $param2 + $param3;
    }
}