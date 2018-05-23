<?php

namespace Mcl\Kaiser;

use Pimple\Container as PimpleContainer;
use Psr\Container\ContainerInterface;

//use Mcl\Kaiser\Logger;
//use Mcl\Kaiser\Request;
//use Mcl\Kaiser\Response;

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

    public function set($id, $value)
    {
        return $this->offsetSet($id, $value);
    }

    private function registerDefaultServices(Container $container)
    {
        $container->set('logger', new  Logger(__DIR__ . '/../log'));
        $container->set('request', new  Request());
        $container->set('response', new  Response());
        $container->set('template', new  \Template_());
    }
}