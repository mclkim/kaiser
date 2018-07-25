<?php

//namespace App;

use Mcl\Db\DBManager;
use Mcl\Kaiser\Controller;

/**
 * composer require mclkim/db
 *
 * http://localhost/mysql
 */
class mysql extends Controller
{
    function execute($request, $response)
    {
        $pdo = $this->container->get('MYSQL');
        $dbm = new DBManager ($pdo);
        var_dump($dbm->executePreparedQueryOne('select version()'));
    }
}