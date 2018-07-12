<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2018-04-22
 * Time: 오전 8:06
 */

namespace Mcl\Kaiser;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


interface ControllerInterface
{
    function requireLogin();

    function requireAdmin();

    function execute(Request $request, Response $response);

    function methods();
}