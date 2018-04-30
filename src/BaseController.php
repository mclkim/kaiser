<?php

namespace Kaiser;

use Psr\Container\ContainerInterface;

class BaseController //extends Singleton
{
    protected $container;

    public function getContainer()
    {
        return $this->container;
    }

    public function setContainer($container = [])
    {
        if (is_array($container)) {
            $container = new Container ($container);
        }
        if (!$container instanceof ContainerInterface) {
            throw new \RuntimeException ('Expected a ContainerInterface');
        }
        $this->container = $container;
    }

    protected function auth()
    {
        return $this->container->get('auth');
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

    protected function template()
    {
        return $this->container->get('template');
    }

}