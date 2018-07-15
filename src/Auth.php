<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2018-04-22
 * Time: 오전 7:36
 */

namespace Mcl\Kaiser;

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

    function getUser()
    {
        return if_exists($_SESSION, $this->_user, false);
    }

    function setUser($user)
    {
        $_SESSION [$this->_user] = $user;
    }

    function getAdmin()
    {
        return if_exists($_SESSION, $this->_admin, false);
    }

    function setAdmin($admin)
    {
        $_SESSION [$this->_admin] = $admin;
    }

    // function checkUser($callable)
    // {
    //     if ($callable->requireLogin()) {
    //         if ($this->getUser() || $this->getAdmin()) {
    //             return true;
    //         }
    //         return false;
    //     }
    //     return true;
    // }

    // function checkAdmin($callable)
    // {
    //     if ($callable->requireAdmin()) {
    //         if ($this->getAdmin()) {
    //             return true;
    //         }
    //         return false;
    //     }
    //     return true;
    // }

    function logout($callable)
    {
        session_unset();
        session_destroy();
        unset ($_SESSION);

        $callable->redirect($this->_defaultPage);
    }

    public function __invoke($request, $response, $next)
    {
        $loggedIn = isset($_SESSION['isLoggedIn']) ? $_SESSION['isLoggedIn'] : 'no';

        if ($loggedIn != 'yes') {
            // If the user is not logged in, redirect them home
//            return $response->withRedirect($this->router->pathFor('login'));
            return $response->redirect('/login');
        }

        // The user must be logged in, so pass this request down the middleware chain
        $response = $next($request, $response);

        // And pass the request back up the middleware chain.
        return $response;
    }
}