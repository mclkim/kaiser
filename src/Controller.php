<?php
/**
 * Created by PhpStorm.
 * User: 김준수
 * Date: 2018-07-05
 * Time: 오후 1:24
 */

namespace Mcl\Kaiser;

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

    function requireAdmin()
    {
        return false;
    }

    function execute($request, $response)
    {
//        echo 'Hello Kaiser PHP framework~~';
        return $response->withStatus(200)->write("Hello Kaiser PHP framework~~");
    }

    function methods()
    {
        return ['GET'];
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