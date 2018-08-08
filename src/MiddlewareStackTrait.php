<?php

/**
 * Created by PhpStorm.
 * User: 김준수
 * Date: 2018-07-31
 * Time: 오전 11:34
 */

namespace Mcl\Kaiser;

use RuntimeException;
use Mcl\Kaiser\RequestInterface as Request;
use Mcl\Kaiser\ResponseInterface as Response;

trait MiddlewareStackTrait
{
    protected $tip;
    protected $middlewareLock = false;

    public function callMiddlewareStack(Request $request, Response $response)
    {
        if (is_null($this->tip)) {
            $this->seedMiddlewareStack();
        }
        $start = $this->tip;
        $this->middlewareLock = true;
        $response = $start($request, $response);
//
//        if ($response instanceof Response)
//            $response->response_sender();

        $this->middlewareLock = false;
        return $response;
    }

    protected function seedMiddlewareStack(callable $kernel = null)
    {
        if ($kernel === null) {
            $kernel = $this;
        }
        $this->tip = $kernel;
    }

    protected function addMiddlewareStack(callable $callable)
    {
        if ($this->middlewareLock) {
            throw new RuntimeException('Middleware can’t be added once the stack is dequeuing');
        }
        if (is_null($this->tip)) {
            $this->seedMiddlewareStack();
        }
        $next = $this->tip;
        $this->tip = function (Request $request, Response $response) use ($callable, $next) {
            $result = call_user_func($callable, $request, $response, $next);
            return $result;
        };
        return $this;
    }
}