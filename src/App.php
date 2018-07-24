<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2018-04-22
 * Time: 오후 3:34
 */

namespace Mcl\Kaiser;

use Psr\Container\ContainerInterface;

class App
{
    const VERSION = '1.5';
    const DATE_APPROVED = '2018-07-15';
    private $container;

    public function __construct($container = [])
    {
        if (is_array($container)) {
            $container = new Container ($container);
        }
        if (!$container instanceof ContainerInterface) {
            throw new \RuntimeException ('Expected a ContainerInterface');
        }
        $this->container = $container;
    }

    function getContainer()
    {
        return $this->container;
    }

    public function __call($method, $args)
    {
        if ($this->container->has($method)) {
            $obj = $this->container->get($method);
            if (is_callable($obj)) {
                return call_user_func_array($obj, $args);
            }
        }

        throw new \BadMethodCallException("Method $method is not a valid method");
    }

    public function __invoke($request, $response)
    {
        echo 'hello world';
    }

    function run($class = [])
    {
        /**
         *
         */
        $request = $this->container->get('request');
        $response = $this->container->get('response');

        $router = new Router();
        $router->setAppDir($class);

        $path = $request->url(PHP_URL_PATH);
        $routeInfo = $router->dispatch($path);
        if ($routeInfo[0] == Router::FOUND) {
            $callable = new $routeInfo[1] ($this->container);
            $this->addRoute($callable->methods(), $path, [$callable, $routeInfo[2]]);
        }

        /**
         *
         */
        $routecollector = $this->container->get('routecollector');
        $dispatcher = new \FastRoute\Dispatcher\GroupCountBased($routecollector->getData());

        $uri = '/' . ltrim($request->url(PHP_URL_PATH), '/');
        $httpMethod = $request->getMethod();

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                $response->status(404, 'Not Found', '1.1');
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                $response->status(405, 'Method Not Allowed', '1.1');
                break;
            case \FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                $res = true;
                if (is_array($handler) && $handler[0] instanceof ControllerInterface) {
                    $auth = new Auth();
                    $res = $auth($handler[0], $request, $response);
                }

                if ($res) {
                    // ... call $handler with $vars
                    call_user_func_array($handler, array($request, $response, $vars));
                }

                break;
        }
        $response->response_sender();
    }

    function addRoute($httpMethod, $route, $handler)
    {
        if (is_callable([$handler, 'setContainer'])) {
            $handler->setContainer($this->container);
        }

        $this->container->get('routecollector')->addRoute($httpMethod, $route, $handler);
    }

    public
    function process($request, $response)
    {
        // Traverse middleware stack
        try {
            $response = $this->callMiddlewareStack($request, $response);
        } catch (Exception $e) {
            $response = $this->handleException($e, $request, $response);
        } catch (Throwable $e) {
            $response = $this->handlePhpError($e, $request, $response);
        }

        return $response;
    }
}