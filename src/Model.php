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
    protected $original_memory_limit;
    protected $original_max_execution_time;

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
        return (array)($token['admin'] ?? null);
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
        return (array)($token['user'] ?? null);
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

    protected function begin()
    {
        // on the beginning of your script save original memory limit
        $this->original_memory_limit = ini_get('memory_limit');

        // then set it to the value you think you need (experiment)
        ini_set('memory_limit', '256M');

        $this->original_max_execution_time = ini_get('max_execution_time');
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
    }

    protected function end()
    {
        // at the end of the script set it to it's original value
        // (if you forget this PHP will do it for you when performing garbage collection)
        ini_set('memory_limit', $this->original_memory_limit);
        ini_set('max_execution_time', $this->original_max_execution_time);
    }
}