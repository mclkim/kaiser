<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2019-12-13
 * Time: 오전 7:36
 */

namespace Mcl\Kaiser;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

if (!function_exists('if_exists')) {
    function if_exists($array, $key, $default = null)
    {
        if (is_array($array) == false) {
            return $default;
        }
        return array_key_exists($key, $array) ? $array [$key] : $default;
    }
}

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

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $request_uri = if_exists($_SERVER, 'X_HTTP_ORIGINAL_URL', $_SERVER ['REQUEST_URI']);
        $return_uri = $request->getParam('returnURI', $request_uri);
        $redirect = implode("/", array_map("rawurlencode", explode("/", $return_uri)));

        $response = $handler->handle($request);
        if ($this->handler->requireAdmin() && empty($this->getAdmin())) {
            return $response->withHeader('Location', $this->_loginAdminPage . '?returnURI=' . $redirect)->withStatus(302);
        } elseif ($this->handler->requireLogin() && empty($this->getUser()) && empty($this->getAdmin())) {
            return $response->withHeader('Location', $this->_loginPage . '?returnURI=' . $redirect)->withStatus(302);
        } elseif ($this->handler->requirePermit() == false) {
            return $response->withStatus(400)->write('Permission denied');
        }
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