<?php
/**
 * Created by PhpStorm.
 * User: 김준수
 * Date: 2018-07-26
 * Time: 오후 2:04
 */

namespace Mcl\Kaiser\Middleware;

use Closure;
use InvalidArgumentException;

class Middleware
{
    private $middlewares;

    public function __construct($middlewares = [])
    {
        $this->middlewares = $middlewares;
    }

    /**
     */
    public function addMiddleware($callable)
    {
        if ($callable instanceof Middleware) {
            $callable = $callable->toArray();
        } elseif ($callable instanceof MiddlewareInterface) {
            $callable = [$callable];
        }

        if (!is_array($callable)) {
            throw new InvalidArgumentException(get_class($callable) . " is not a valid middleware.");
        }

        $this->middlewares += $callable;
        return $this;
    }

    /**
     */
    private function toArray()
    {
        return $this->middlewares;
    }

    /**
     */
    public function callMiddleware($request, $response, $callable, $args = null)
    {
//        var_dump(is_callable($callable));
        if ($callable instanceof Closure) {
            $callFunction = $this->createCallFunction($callable);
        } elseif (is_callable($callable)) {
            $callFunction = $callable;
        }

        // Since we will be "currying" the functions starting with the first
        // in the array, the first function will be "closer" to the Call.
        // This also means it will be run last. However, if the reverse the
        // order of the array, the first in the list will be the outer middlewares.
        $middlewares = array_reverse($this->middlewares);

        // We create the onion by starting initially with the Call and then
        // gradually wrap it in middlewares.
        // Each layer will have the next layer "curried"
        // into it and will have the current state (the object) passed to it.
        $callable = array_reduce($middlewares, function ($next, $middleware) {
            return $this->createLayer($next, $middleware);
        }, $callFunction);

        // We now have the complete onion and can start passing the object
        // down through the middlewares.
        if ($callable instanceof Closure) {
            return $callable($request, $response);
        } elseif (is_callable($callable)) {
            return call_user_func($callable, $request, $response, $args);
        }
    }

    /**
     */
    private function createCallFunction($callable)
    {
        return function ($request, $response) use ($callable) {
            return $callable($request, $response);
        };
    }

    /**
     */
    private function createLayer($next, $middleware)
    {
        return function ($request, $response) use ($next, $middleware) {
            return $middleware->__invoke($request, $response, $next);
        };
    }
}