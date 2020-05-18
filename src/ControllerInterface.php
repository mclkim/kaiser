<?php
/**
 * @link      https://github.com/mclkim/kaiser
 * @copyright Copyright (p) myung chul kim
 * @license   MIT License
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