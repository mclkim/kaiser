<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2018-04-22
 * Time: 오전 7:36
 */

namespace Mcl\Kaiser;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Router;

class Auth
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
    var $_loginAdminPage = '/admin.login';
    var $_admin = 'admin';
    var $_user = 'user';
    private $handler;

    function __construct($handler = null)
    {
        if ($handler instanceof ControllerInterface) $this->handler = $handler;
    }

    function __invoke(Request $request, Response $response, $next)
    {
        $res = true;

        if ($this->handler->requireAdmin()) {
            $res = $this->checkAdmin($request, $response);
        } elseif ($this->handler->requireLogin()) {
            $res = $this->checkUser($request, $response);
        }

        if ($res === true) {
            // The user must be logged in, so pass this request down the middleware chain
            $response = $next($request, $response);

            // And pass the request back up the middleware chain.
            return $response;
        }
        return $res;
    }

    function checkAdmin($request, $response)
    {
        if ($this->getAdmin()) return true;

        $request_uri = if_exists($_SERVER, 'X_HTTP_ORIGINAL_URL', $_SERVER ['REQUEST_URI']);
        $return_uri = $request->getQueryParam('returnURI', $request_uri);
        $redirect = implode("/", array_map("rawurlencode", explode("/", $return_uri)));
        return $response->withRedirect($this->_loginAdminPage . '?returnURI=' . $redirect, 301);
    }

    function getAdmin()
    {
        return if_exists($_SESSION, $this->_admin, false);
    }

    function setAdmin($admin)
    {
        $_SESSION [$this->_admin] = $admin;
    }

    function checkUser($request, $response)
    {
        if ($this->getAdmin() || $this->getUser()) return true;

        $request_uri = if_exists($_SERVER, 'X_HTTP_ORIGINAL_URL', $_SERVER ['REQUEST_URI']);
        $return_uri = $request->getQueryParam('returnURI', $request_uri);
        $redirect = implode("/", array_map("rawurlencode", explode("/", $return_uri)));
        return $response->withRedirect($this->_loginPage . '?returnURI=' . $redirect, 301);
    }

    function getUser()
    {
        return if_exists($_SESSION, $this->_user, false);
    }

    function setUser($user)
    {
        $_SESSION [$this->_user] = $user;
    }
}