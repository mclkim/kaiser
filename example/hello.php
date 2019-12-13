<?php

use Mcl\Kaiser\Controller;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

/**
 * http://localhost/hello.world?p1=1&p2=2&p3=3
 */
class hello extends Controller
{
    function world(ServerRequest $request, Response $response): Response
    {
        $getParams = $request->getParams();
        var_dump($getParams);
//        var_dump($this->container);
//        $logger = $this->container->get('logger');
//        $logger = $this->container->get(LoggerInterface::class);
//        $logger->info('hello world');
        $this->info('', $getParams);
        echo '<br>';
        echo 'hello world~~~';
        return $response;
    }
}