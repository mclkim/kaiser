<?php
/**
 * Created by PhpStorm.
 * User: 김준수
 * Date: 2018-07-05
 * Time: 오후 1:24
 */

namespace Mcl\Kaiser;

<<<<<<< HEAD
class Controller implements ControllerInterface
{
    protected $container;

    function __construct($container = [])
    {
        $this->container = $container;
    }

    /**
     * 로그인 여부를 체크 할 페이지 인지에 대한 세팅을 한다.
     * true를 리턴하면 로그인 체크를 하며, false를 리턴할 경우 로그인 체크를 하지 않는다.
     * 개발자가 해당 페이지가 로그인 해야 하는 페이지라면 true를 리턴 하도록 overriding 한다.
     */
    function requireLogin()
    {
        return false;
    }

=======
use Interop\Container\ContainerInterface;

class Controller implements ControllerInterface
{
    protected $container;

    public function __construct($container = [])
    {
        $this->container = $container;
    }

    function requireLogin()
    {
        return false;
    }

>>>>>>> 26aa3e402fdbd25eed47c460755f00c907c00a92
    function requireAdmin()
    {
        return false;
    }

    function execute($request, $response)
    {
<<<<<<< HEAD
        return $response->status(200)->setContent('OK! Kaiser PHP Framework');
=======
//        echo 'Hello Kaiser PHP framework~~';
        return $response->withStatus(200)->write("Hello Kaiser PHP framework~~");
>>>>>>> 26aa3e402fdbd25eed47c460755f00c907c00a92
    }

    function methods()
    {
        return ['GET'];
<<<<<<< HEAD
    }

    /**
     * 로그아웃
     */
    function logout()
    {
=======
>>>>>>> 26aa3e402fdbd25eed47c460755f00c907c00a92
    }

    function info($message = null, array $context = array())
    {
        $this->container->logger->info($message, $context);
    }

    function debug($message = null, array $context = array())
    {
        $this->container->logger->debug($message, $context);
    }

    function err($message = null, array $context = array())
    {
        $this->container->logger->error($message, $context);
    }
}