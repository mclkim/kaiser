<?php
use \Kaiser\Controller;

/**
 * http://localhost/test/public/?hello.world
 */
class hello extends Controller
{
    protected function requireLogin()
    {
        return false;
    }

    function world()
    {
        echo 'hello world~~~';
    }
}