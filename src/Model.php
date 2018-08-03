<?php
/**
 * Created by PhpStorm.
 * User: 김준수
 * Date: 2018-07-05
 * Time: 오후 1:24
 */

namespace Mcl\Kaiser;

abstract class Model
{
    protected $container;

    function __construct($container = [])
    {
        $this->container = $container;
    }

    function debug($message = null, array $context = array())
    {
        $this->container->logger->debug($message, $context);
    }

    function info($message = null, array $context = array())
    {
        $this->container->logger->info($message, $context);
    }

    function err($message = null, array $context = array())
    {
        $this->container->logger->error($message, $context);
    }
}