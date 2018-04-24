<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2018-04-23
 * Time: 오후 1:46
 */

namespace Kaiser;

use Pimple\Container as PimpleContainer;
use Psr\Container\ContainerInterface;

final class Container extends PimpleContainer implements ContainerInterface
{
    function __construct($values = array())
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
        $container->set('auth', new  Auth ());
        $container->set('config', new  Config ());
        $container->set('request', new  Request ());
        $container->set('response', new  Response ());
        $container->set('router', new  Router ());
        $container->set('template', new  \Template_ ());
        /**
         * TODO::
         * KLogger: Simple Logging for PHP
         * https://github.com/katzgrau/KLogger
         */
        $container->set('logger',
            $container->factory(function ($c) {
                return new Manager\LogManager (__DIR__ . '/../log');
            })
        );
    }
}