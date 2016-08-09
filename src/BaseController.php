<?php

namespace Kaiser;

// use \Kaiser\Exception\ApplicationException;

class BaseController extends Singleton
{
    protected $container;

    function __construct($container = [])
    {
        if (is_array($container)) {
            $container = new Container ($container);
        }
        if (!$container instanceof ContainerInterface) {
            // exit ( 'Expected a ContainerInterface' );
            throw new \RuntimeException ('Expected a ContainerInterface');
        }
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    protected function setContainer($id, $value)
    {
        return $this->container->offsetSet($id, $value);
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