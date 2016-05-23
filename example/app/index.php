<?php
use \Kaiser\Controller;

/**
 * http://localhost/test/public/?index
 */
class index extends Controller
{
    protected function requireLogin()
    {
        return false;
    }

    function execute()
    {
        echo 'Kaiser PHP framework~~';
    }
}