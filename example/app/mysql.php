<?php

use Mcl\Db\DBManager;
use Mcl\Kaiser\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * composer require mclkim/db
 *
 * http://localhost/mysql
 */
class mysql extends Controller
{
    function execute(Request $request, Response $response)
    {
        $pdo = $this->container->get('MYSQL');
        $dbm = new DBManager ($pdo);
        var_dump($dbm->executePreparedQueryOne('select version()'));
    }
}