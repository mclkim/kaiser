<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2019-12-13
 * Time: 오전 7:36
 */

namespace Mcl\Kaiser;

use Slim\Http\Response;
use Slim\Http\ServerRequest;

interface ControllerInterface
{
    function requireLogin();

    function requireAdmin();

    function requirePermit();

    function execute(ServerRequest $request, Response $response): Response;

    function methods();
}