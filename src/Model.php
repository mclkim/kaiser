<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2019-12-13
 * Time: 오전 7:36
 */

namespace Mcl\Kaiser;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

abstract class Model
{
    protected $container;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    function debug($message = null, array $context = array())
    {
        $logger = $this->container->get(LoggerInterface::class);
        $logger->debug($message, $context);
    }

    function info($message = null, array $context = array())
    {
        $logger = $this->container->get(LoggerInterface::class);
        $logger->info($message, $context);
    }

    function err($message = null, array $context = array())
    {
        $logger = $this->container->get(LoggerInterface::class);
        $logger->error($message, $context);
    }
}