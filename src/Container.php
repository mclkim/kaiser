<?php

namespace Kaiser;

use Pimple\Container as PimpleContainer;
use Psr\Container\ContainerInterface;

class Container extends PimpleContainer implements ContainerInterface
{
    function __construct(array $values = [])
    {
        parent::__construct($values);

        $this->registerDefaultServices($this);
    }

    public function get($id)
    {
        if (!$this->offsetExists($id)) {
            throw new \RuntimeException (sprintf('Identifiler "%s" is not defined.', $id));
        }
        return $this->offsetGet($id);
    }

    public function has($id)
    {
        return $this->offsetExists($id);
    }

    public function set(string $id, $value)
    {
        return $this->offsetSet($id, $value);
    }

    private function registerDefaultServices(Container $container)
    {
        $container->set('auth', new  Auth());
        $container->set('config', new  Config());
        $container->set('request', new  Request());
        $container->set('response', new  Response());
        $container->set('router', new  Router());
        $container->set('template', new  \Template_());
        /**
         * KLogger: Simple Logging for PHP
         * https://github.com/katzgrau/KLogger
         */
        $container->set('logger', new  Manager\LogManager (__DIR__ . '/../log'));
    }
}