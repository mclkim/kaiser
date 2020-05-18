<?php
/**
 * @link      https://github.com/mclkim/kaiser
 * @copyright Copyright (p) myung chul kim
 * @license   MIT License
 */

namespace Mcl\Kaiser;


use Slim\Http\Response;
use Slim\Http\ServerRequest;

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

    /**
     * TODO::2019-06-13
     * 프로그램 접근 권한 수정
     */
    function requirePermit()
    {
        return true;
    }

    function execute(ServerRequest $request, Response $response): Response
    {
        $response->withStatus(200)->write('OK! Kaiser PHP Framework');
        return $response;
    }

    function methods()
    {
        return ['GET'];
    }
}
