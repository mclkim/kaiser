<?php
/**
 * Created by PhpStorm.
 * User: 김준수
 * Date: 2018-07-26
 * Time: 오후 2:04
 */

namespace Mcl\Kaiser\Middleware;

class Middleware
{
    private $middlewares;

    public function __construct(array $middlewares = [])
    {
        $this->middlewares = $middlewares;
    }

    /**
     */
    public function addMiddleware($middlewares)
    {
        if ($middlewares instanceof Middleware) {
            $middlewares = $middlewares->toArray();
        }

        if ($middlewares instanceof MiddlewareInterface) {
            $middlewares = [$middlewares];
        }

        if (!is_array($middlewares)) {
            throw new InvalidArgumentException(get_class($middlewares) . " is not a valid middleware.");
        }

        $this->middlewares = array_merge($this->middlewares, $middlewares);
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
    public function __invoke($request, $response, $core)
    {
        if ($core instanceof Closure)
            $coreFunction = $this->createCoreFunction($core);

        else//if (is_callable([$core]))
            $coreFunction = $core;

        // Since we will be "currying" the functions starting with the first
        // in the array, the first function will be "closer" to the core.
        // This also means it will be run last. However, if the reverse the
        // order of the array, the first in the list will be the outer middlewares.
        $middlewares = array_reverse($this->middlewares);

        // We create the onion by starting initially with the core and then
        // gradually wrap it in middlewares.
        // Each layer will have the next layer "curried"
        // into it and will have the current state (the object) passed to it.
        $completeOnion = array_reduce($middlewares, function ($next, $middleware) {
            return $this->createLayer($next, $middleware);
        }, $coreFunction);

        // We now have the complete onion and can start passing the object
        // down through the middlewares.
        if ($completeOnion instanceof Closure)
            return $completeOnion($request, $response);

        elseif (is_callable([$completeOnion]))
            return call_user_func_array([$completeOnion], array($request, $response));
    }

    /**
     */
    private function createCoreFunction($core)
    {
        return function ($request, $response) use ($core) {
            return $core($request, $response);
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