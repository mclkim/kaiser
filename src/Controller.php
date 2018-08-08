<?php

namespace Mcl\Kaiser;

abstract class Controller extends Model implements ControllerInterface
{
    /**
     * 로그인 여부를 체크 할 페이지 인지에 대한 세팅을 한다.
     * true를 리턴하면 로그인 체크를 하며, false를 리턴할 경우 로그인 체크를 하지 않는다.
     * 개발자가 해당 페이지가 로그인 해야 하는 페이지라면 true를 리턴 하도록 overriding 한다.
     */
    function requireLogin()
    {
        return false;
    }

    function requireAdmin()
    {
        return false;
    }

    function execute($request, $response)
    {
//        return $response->status(200)->setContent('OK! Kaiser PHP Framework');
        return $response->withStatus(200)->write('OK! Kaiser PHP Framework');
    }

    function methods()
    {
        return ['GET'];
    }

    /**
     * 로그인
     */
    function login($request, $response)
    {
    }

    /**
     * 로그아웃
     */
    function logout($request, $response)
    {
    }

}
