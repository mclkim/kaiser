<?php
/**
 * Created by PhpStorm.
 * User: 김준수
 * Date: 2018-07-11
 * Time: 오후 1:34
 */

namespace Mcl\Kaiser;

/*
 * composer require slim/slim "^3.0"
 */
use Slim\App as Slim;

class App extends Slim
{
    const VERSION = '2018-07-11';

    public function run($directory = [], $silent = false)
    {
        $request = $this->getContainer()->get('request');
        $path = $request->getUri()->getPath();

        $router = new Router();
        $router->setContainer($this->getContainer());
        $router->setAppDir($directory);
        $routeInfo = $router->dispatch($path);

        $this->debug('routeInfo', $routeInfo);

        //TODO::
        $controller = $routeInfo[1];
        $action = $routeInfo[2];
        $parameters = $routeInfo[3];

        switch ($routeInfo[0]) {
            case Router::NOT_FOUND:
                break;
            case Router::NOT_FOUND_ACTION:
                break;
            case Router::FOUND:
                //TODO::
                $handler = new $controller($this->getContainer());

                if ($handler->requireAdmin()) {
                    $this->map($handler->methods(), $path, $controller . ':' . $action)->add('Auth');
                } elseif ($handler->requireLogin()) {
                    $this->map($handler->methods(), $path, $controller . ':' . $action)->add('Auth');
                } else {
                    $this->map($handler->methods(), $path, $controller . ':' . $action);
                }
        }

        parent::run($silent);
    }

    function debug($message = null, array $context = array())
    {
        $this->getContainer()->get('logger')->debug($message, $context);
    }

    function info($message = null, array $context = array())
    {
        $this->getContainer()->get('logger')->info($message, $context);
    }

    function err($message = null, array $context = array())
    {
        $this->getContainer()->get('logger')->error($message, $context);
    }
}