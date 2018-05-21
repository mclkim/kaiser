<?php

use Mcl\Kaiser\Controller;

/**
 * http://localhost/?index
 */
class index extends Controller
{
    function requireLogin()
    {
        return false;
    }

    function execute()
    {
        echo 'Kaiser PHP framework~~';
    }
}