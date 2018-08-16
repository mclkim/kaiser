<?php
/**
 * @link      https://github.com/mclkim/kaiser
 * @copyright Copyright (p) myung chul kim
 * @license   MIT License
 */

namespace Mcl\Kaiser;

use Psr\Container\ContainerInterface;

class App extends \Slim\App
{
    const VERSION = 'v20180808';
    const DATE_APPROVED = '2018-08-08';

    public function __construct($container = [])
    {
        parent::__construct($container);
        $this->registerDefaultServices($this->getContainer());
    }

    private function registerDefaultServices($container)
    {
        /**
         * TODO::
         */
        $reqServer = $_SERVER;
        $reqServer['SCRIPT_NAME'] = "/{$_SERVER['SCRIPT_NAME']}";
        /**
         * Add Overide on Application initial
         * set container request with new value of \Slim\Http\Request with new values
         */
        $container['request'] = \Slim\Http\Request::createFromEnvironment(
            \Slim\Http\Environment::mock(
                $reqServer
            )
        );

        $container['logger'] = function ($container) {
            return new Logger(__DIR__ . '/../log');
        };

//        $container['template'] = function ($container) {
//            return new \Template_();
//        };
    }

    function run($appMap = ['App\\' => 'app'])
    {
        /**
         *TODO::
         */
        $container = $this->getContainer();
        $request = $container->get('request');
        $response = $container->get('response');

        $router = new Router();
        $router->setAppMap($appMap);

        $path = $request->getUri()->getPath();
        $routeInfo = $router->dispatch($path);
//        var_dump($routeInfo);
        if (is_array($routeInfo) && $routeInfo[0] == Router::FOUND) {
            $callable = new $routeInfo[1] ($container);
            if ($callable instanceof ControllerInterface) {
                $this->add(new Auth($callable));
                $this->map($callable->methods(), $path, [$callable, $routeInfo[2]]);
            }
        }

        parent::run();
    }
}