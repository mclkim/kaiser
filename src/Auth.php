<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2018-04-22
 * Time: 오전 7:36
 */

namespace Mcl\Kaiser;

use Mcl\Kaiser\RequestInterface as Request;
use Mcl\Kaiser\ResponseInterface as Response;

class Auth
{
//    var $admin = [
//        'username' => 'admin',//사용자명(아이디)
//        'password' => 'X',//패스워드
//        'uid' => '0',//사용자ID
//        'gid' => '0',//그룹ID
//        'comment' => '',//정보
//        'home' => '/admin',//홈디렉토리
//        'defaultPage' => '/admin',//홈페이지
//        'hashed_password' => '',//해쉬비밀번호
//        'salt' => '',//비밀번호암호키
//    ];

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
        if ($this->handler->requireAdmin()) {
            $res = $this->checkAdmin($request, $response);
        } elseif ($this->handler->requireLogin()) {
            $res = $this->checkUser($request, $response);
        }
        return $next($request, $response);
    }

    function checkAdmin($request, $response)
    {
        if ($this->getAdmin()) return true;

        $request_uri = if_exists($_SERVER, 'X_HTTP_ORIGINAL_URL', $_SERVER ['REQUEST_URI']);
        $return_uri = $request->get('returnURI', $request_uri);
        $redirect = implode("/", array_map("rawurlencode", explode("/", $return_uri)));
        $response->redirect($this->_loginAdminPage . '?returnURI=' . $redirect);
        return false;
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
        $return_uri = $request->get('returnURI', $request_uri);
        $redirect = implode("/", array_map("rawurlencode", explode("/", $return_uri)));
        $response->redirect($this->_loginPage . '?returnURI=' . $redirect);
        return false;
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