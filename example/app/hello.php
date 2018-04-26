<?php

use Kaiser\Controller;

/**
 * http://localhost/?hello.world&p1=1&p2=2&p3=3
 */
class hello extends Controller
{
    function requireLogin()
    {
        return false;
    }

    function world($params)
    {
        $getData = $this->getParameters();
        $this->debug($getData);

        var_dump($params);
        echo 'hello world~~~';
    }
}