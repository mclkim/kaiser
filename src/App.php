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

    public function __construct($container = [])
    {
        parent::__construct($container);
        $this->registerDefaultServices($this->getContainer());
    }

    public function run($directory = [], $silent = false)
    {
//        var_dump($_SERVER);
//        phpinfo();
//        exit;
        $request = $this->getContainer()->get('request');
        $path = $request->getUri()->getPath();

        $router = new Router($this->getContainer());
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

                //                $path = str_replace('.', '/', $path);
                if ($handler->requireAdmin()) {
                    $this->map($handler->methods(), $path, $controller . ':' . $action)->add('Auth');
                } elseif ($handler->requireLogin()) {
                    $this->map($handler->methods(), $path, $controller . ':' . $action)->add('Auth');
                } else {
                    $this->map($handler->methods(), $path, $controller . ':' . $action);
                }
        }
        /**
         * $_SERVER['QUERY_STRING']    p1=1&p2=2&p3=3
         * $_SERVER['REQUEST_URI']    /hello.world?p1=1&p2=2&p3=3
         */
//        $this->map(['GET'], '/', '\App\index:execute');
//        $this->map(['GET'], '/hello.world', '\App\hello:world');
//        $this->map(['GET'], '/hello-world', '\App\hello:world');
//        $this->map(['GET'], '/hello@world', '\App\hello:world');
//        $this->map(['GET'], '/hello&world', '\App\hello:world');
//        $this->map(['GET'], '/hello.execute', '\App\hello:execute');
//        $this->map(['GET'], '/hello', '\App\hello:execute');

        parent::run($silent);
    }

    private function registerDefaultServices($container)
    {
        $reqServer = $_SERVER;
        $reqServer['SCRIPT_NAME'] = "/{$_SERVER['SCRIPT_NAME']}";
        /**
         * Add Overide on Application initial
         * set container request with new value of \Slim\Http\Request
         *  with new values
         */
        $container['request'] = \Slim\Http\Request::createFromEnvironment(
            \Slim\Http\Environment::mock(
                $reqServer
            )
        );

        $container['logger'] = function ($container) {
            return new Logger(__DIR__ . '/../log');
        };

        $container['template'] = function ($container) {
            return new \Template_();
        };
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