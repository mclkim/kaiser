<?php
/**
 * @link      https://github.com/mclkim/kaiser
 * @copyright Copyright (p) myung chul kim
 * @license   MIT License
 */

namespace Mcl\Kaiser;

use Psr\Container\ContainerInterface;

class App
{
    use MiddlewareStackTrait;

    const VERSION = '20180715';
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

    //TODO::
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

    function run($appMap = ['App\\' => 'app'])
    {
        /**
         *TODO::
         */
        $request = $this->container->get('request');
        $response = $this->container->get('response');

        $router = new Router();
        $router->setAppMap($appMap);

        $path = $request->url(PHP_URL_PATH);
        $routeInfo = $router->dispatch($path);
        if (is_array($routeInfo) && $routeInfo[0] == Router::FOUND) {
            $callable = new $routeInfo[1] ($this->container);
            if ($callable instanceof ControllerInterface) {
                $this->add(new Auth($callable));
                $this->addRoute($callable->methods(), $path, [$callable, $routeInfo[2]]);
            }
        }

        $this->process($request, $response);
        $response->response_sender();
    }

    public function add($callable)
    {
        return $this->addMiddlewareStack($callable);
    }

    function addRoute($httpMethod, $route, $handler)
    {
        $route = $this->container->get('routecollector')->addRoute($httpMethod, $route, $handler);

        return $route;
    }

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

    public function __invoke($request, $response)
    {
        $routecollector = $this->container->get('routecollector');
        $dispatcher = new \FastRoute\Dispatcher\GroupCountBased($routecollector->getData());

        // Fetch method and URI from somewhere
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

//        $uri = '/' . ltrim($request->url(PHP_URL_PATH), '/');
//        $httpMethod = $request->method();

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
                //TODO::
                call_user_func($handler, $request, $response, $vars);
                break;
        }
    }

    public function get($route, $handler)
    {
        $this->addRoute(['GET'], $route, $handler);
        return $this;
    }
}