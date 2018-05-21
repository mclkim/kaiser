<?php

use Mcl\Kaiser\Controller;
use Mcl\Db\DBManager;

/**
 * composer require mclkim/db
 *
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
        $pdo = $this->container->get('MYSQL');
        $dbm = new DBManager ($pdo);
        var_dump($dbm->executePreparedQueryOne('select version()'));
    }
}