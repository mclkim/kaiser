<?php

namespace App;

use Mcl\Kaiser\Controller;

/**
 * http://localhost/
 */
class index extends Controller
{
    function execute($request, $response)
    {
        echo 'Kaiser PHP framework~~';
    }
}