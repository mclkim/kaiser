<?php

namespace Kaiser;

// use Kaiser\Exception\ApplicationException;
//use Pimple\Container as PimpleContainer;

class BaseController extends Singleton
{
    protected $container;

    function __construct($container)
    {
        if (is_array($container)) {
            $container = new Container ($container);
        }

        $this->container = $container;
//        $this->registerServices($container);
    }

    public function getContainer()
    {
        return $this->container;
    }

    protected function config()
    {
        return $this->container->get('config');
    }

    protected function logger()
    {
        return $this->container->get('logger');
    }

    protected function request()
    {
        return $this->container->get('request');
    }

    protected function response()
    {
        return $this->container->get('response');
    }

    protected function router()
    {
        return $this->container->get('router');
    }

}