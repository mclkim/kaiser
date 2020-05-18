<?php
declare(strict_types=1);

/**
 * @link      https://github.com/mclkim/kaiser
 * @copyright Copyright (p) myung chul kim
 * @license   MIT License
 */

namespace Mcl\Kaiser;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class Model
{
    protected $container;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getAdmin()
    {
        $token = self::getToken();
        return (array)($token['data'] ?? null);
    }

    public function getToken()
    {
        $token = self::getJWT();
        return (array)($token['decoded'] ?? null);
    }

    public function getJWT()
    {
        return $this->container->has("jwt") ? $this->container->get("jwt") : null;
    }

    public function getUser()
    {
        $token = self::getToken();
        return (array)($token['data'] ?? null);
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