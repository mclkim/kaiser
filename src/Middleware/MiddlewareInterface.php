<?php

namespace Mcl\Kaiser\Middleware;
/**
 * Created by PhpStorm.
 * User: 김준수
 * Date: 2018-07-26
 * Time: 오후 2:03
 */
interface MiddlewareInterface
{
    public function __invoke($request, $response, $next);
}