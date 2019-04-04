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
    var $_loginAdminPage = '/login.admin';
    var $_admin = 'admin';
    var $_user = 'user';
    private $handler;

    function __construct($handler = null)
    {
        if ($handler instanceof ControllerInterface) $this->handler = $handler;
    }

    function __invoke(Request $request, Response $response, $next)
    {
        $request_uri = if_exists($_SERVER, 'X_HTTP_ORIGINAL_URL', $_SERVER ['REQUEST_URI']);
        $return_uri = $request->getParam('returnURI', $request_uri);
        $redirect = implode("/", array_map("rawurlencode", explode("/", $return_uri)));

        if ($this->handler->requireAdmin() && empty($this->getAdmin())) {
            return $response->withRedirect($this->_loginAdminPage . '?returnURI=' . $redirect, 301);
        } elseif ($this->handler->requireLogin() && empty($this->getUser()) && empty($this->getAdmin())) {
            return $response->withRedirect($this->_loginPage . '?returnURI=' . $redirect, 301);
        }

        // The user must be logged in, so pass this request down the middleware chain
        $response = $next($request, $response);

        // And pass the request back up the middleware chain.
        return $response;
    }

    function getAdmin()
    {
        return empty($_SESSION) ? '' : if_exists($_SESSION, $this->_admin, false);
    }

    function setAdmin($admin)
    {
        $_SESSION [$this->_admin] = $admin;
    }

    function getUser()
    {
        return empty($_SESSION) ? '' : if_exists($_SESSION, $this->_user, false);
    }

    function setUser($user)
    {
        $_SESSION [$this->_user] = $user;
    }
}