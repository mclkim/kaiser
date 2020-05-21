<?php
declare(strict_types=1);

namespace Mcl\Kaiser\Middleware;

use Mcl\Kaiser\ControllerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class JwtAuthMiddleware implements Middleware
{
    var $_loginAdminPage = '/login.admin';
    var $_loginPage = '/login';
    var $_admin = 'admin';
    var $_user = 'user';

    private $handler;

    function __construct($handler = null)
    {
        if ($handler instanceof ControllerInterface) $this->handler = $handler;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $req_uri = (string)($_SERVER['X_HTTP_ORIGINAL_URL'] ?? $_SERVER ['REQUEST_URI']);
        $ret_uri = $request->getParam('returnURI', $req_uri);
        $redirect = implode("/", array_map("rawurlencode", explode("/", $ret_uri)));

        /* Everything ok, call next middleware. */
        $response = $handler->handle($request);

        if ($this->handler->requireAdmin() && empty($this->getAdmin())) {
            return $response->withHeader('Location', $this->_loginAdminPage . '?returnURI=' . $redirect)->withStatus(302);
        } else if ($this->handler->requireLogin() && empty($this->getUser()) && empty($this->getAdmin())) {
            return $response->withHeader('Location', $this->_loginPage . '?returnURI=' . $redirect)->withStatus(302);
        } else if ($this->handler->requirePermit() == false) {
            $result = ['error' => ['message' => 'Permission denied']];
            return $response->withJson($result)->withStatus(400);
        }

        return $response;
    }

    function getAdmin()
    {
        $token = $this->handler->getToken();
        return (array)($token[$this->_admin] ?? null);
    }

    function getUser()
    {
        $token = $this->handler->getToken();
        return (array)($token[$this->_user] ?? null);
    }
}
