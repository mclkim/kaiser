<?php
declare(strict_types=1);

/**
 * @link      https://github.com/mclkim/kaiser
 * @copyright Copyright (p) myung chul kim
 * @license   MIT License
 */

namespace Mcl\Kaiser\Middleware;

use Mcl\Kaiser\ControllerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthMiddleware implements Middleware
{
    var $admin = [
        'username' => 'admin',//사용자명(아이디)
        'password' => 'X',//패스워드
        'uid' => '0',//사용자ID
        'gid' => '0',//그룹ID
        'comment' => '',//정보
        'home' => '/admin',//홈디렉토리
        'defaultPage' => '/admin',//홈페이지
        'hashed_password' => '',//해쉬비밀번호
        'salt' => '',//비밀번호암호키
    ];

    var $_defaultPage = '/';
    var $_defaultAdminPage = '/admin';
    var $_loginPage = '/login';
    var $_loginAdminPage = '/login.admin';
    var $_admin = 'admin';
    var $_user = 'user';

    private $handler;

    function __construct($handler = null)
    {
        if ($handler instanceof ControllerInterface) $this->handler = $handler;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $request_uri = (string)($_SERVER['X_HTTP_ORIGINAL_URL'] ?? $_SERVER ['REQUEST_URI']);
        $return_uri = $request->getParam('returnURI', $request_uri);
        $redirect = implode("/", array_map("rawurlencode", explode("/", $return_uri)));

        /* Everything ok, call next middleware. */
        $response = $handler->handle($request);

        if ($this->handler->requireAdmin() && empty($this->getAdmin())) {
            return $response->withHeader('Location', $this->_loginAdminPage . '?returnURI=' . $redirect)->withStatus(302);
        } elseif ($this->handler->requireLogin() && empty($this->getUser()) && empty($this->getAdmin())) {
            return $response->withHeader('Location', $this->_loginPage . '?returnURI=' . $redirect)->withStatus(302);
        } else if ($this->handler->requirePermit() == false) {
            $result = ['error' => ['message' => 'Permission denied']];
            return $response->withJson($result)->withStatus(400);
        }

        return $response;
    }

    function getAdmin()
    {
        return (array)($_SESSION[$this->_admin] ?? null);
    }

    function setAdmin($admin)
    {
        $_SESSION [$this->_admin] = $admin;
    }

    function getUser()
    {
        return (array)($_SESSION[$this->_user] ?? null);
    }

    function setUser($user)
    {
        $_SESSION [$this->_user] = $user;
    }
}