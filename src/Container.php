<?php

namespace Mcl\Kaiser;

use Pimple\Container as PimpleContainer;
use Psr\Container\ContainerInterface;

class Container extends PimpleContainer implements ContainerInterface
{
    function __construct(array $values = [])
    {
        parent::__construct($values);

        $this->registerDefaultServices($this);
    }

    private function registerDefaultServices(Container $container)
    {
        $container->set('logger', new  Logger(__DIR__ . '/../log'));
        $container->set('request', new  Request());
        $container->set('response', new  Response());
        $container->set('template', new  \Template_());

        $container['routecollector'] = function ($container) {
            return new \FastRoute\RouteCollector(new \FastRoute\RouteParser\Std, new \FastRoute\DataGenerator\GroupCountBased);
        };

        $container['notFoundHandler'] = function ($container) {
            return new NotFound;
        };
    }

    public function set($id, $value)
    {
        return $this->offsetSet($id, $value);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function get($id)
    {
        if (!$this->offsetExists($id)) {
            throw new \RuntimeException (sprintf('Identifiler "%s" is not defined.', $id));
        }
        return $this->offsetGet($id);
    }

    public function __isset($name)
    {
        return $this->has($name);
    }

    public function has($id)
    {
        return $this->offsetExists($id);
    }

}
