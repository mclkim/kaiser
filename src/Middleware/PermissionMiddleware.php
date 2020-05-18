<?php
declare(strict_types=1);

namespace Mcl\Kaiser\Middleware;

use Mcl\Kaiser\ControllerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class PermissionMiddleware implements Middleware
{
    private $handler;

    function __construct($handler = null)
    {
        if ($handler instanceof ControllerInterface) $this->handler = $handler;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        /* Everything ok, call next middleware. */
        $response = $handler->handle($request);

        if ($this->handler->requirePermit() == false) {
            $result = ['error' => ['message' => 'Permission denied']];
            return $response->withJson($result)->withStatus(400);
        }

        return $response;
    }
}
