<?php

use Kaiser\Controller;

/**
 * http://localhost/?mysql
 */

class mysql extends Controller
{
    function requireLogin()
    {
        return false;
    }

    function execute()
    {
        $pdo = $this->container->get('DB');
        $dbm = new \Kaiser\Manager\DBManager ($pdo);
        var_dump($dbm->executePreparedQueryOne('select version()'));
    }
}