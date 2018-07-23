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

    public function version()
    {
        return self::VERSION;
    }

    public function __invoke($request, $response)
    {
        echo 'hello world';
    }

    function run3($prefix = '\App')
    {
        // Fetch method and URI from somewhere
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routecollector = $this->container->get('routecollector');
        $dispatcher = new \FastRoute\Dispatcher\GroupCountBased($routecollector->getData());

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
//        var_dump($routeInfo);
//exit;
        $request = $this->container->get('request');
        $response = $this->container->get('response');
        call_user_func_array($handler, array($request, $response, $vars));
        $response->response_sender();
    }

    function run2($prefix = '\App')
    {
        // project-specific namespace prefix
        $prefix = trim($prefix, '\\');

        $request = $this->container->get('request');
        $response = $this->container->get('response');
        $this->url = $request->url(PHP_URL_PATH);

        $route = new Route($this->url);
        $route->getRoute();

        $controller = $route->getController();
        $this->action = $route->getAction();
        $parameters = $route->getParameters();
        $handler = $prefix . str_replace('/', '\\', $controller);

//        var_dump(new $handler);exit;
        $callable = new $handler();
        $this->addRoute($callable->methods(), $this->url, $callable);

        /**
         *
         */
        // Fetch method and URI from somewhere
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routecollector = $this->container->get('routecollector');
        $dispatcher = new \FastRoute\Dispatcher\GroupCountBased($routecollector->getData());

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                break;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                break;
            case \FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                $result = true;
                $auth = new Auth();
                $res = $auth($handler, $request, $response);

                if ($res) {
                    // ... call $handler with $vars
                    $result = call_user_func_array(array($handler, $this->action), array($request, $response, $vars));
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

    public function run($directory = [])
    {
        $request = $this->container->get('request');
        $response = $this->container->get('response');

        $router = new Router();
        $router->setAppDir($directory);
        $path = $request->url(PHP_URL_PATH);
        $routeInfo = $router->dispatch($path);

        $this->container->logger->debug('routeInfo', $routeInfo);

        switch ($routeInfo[0]) {
            case Router::NOT_FOUND:
                // ... 404 Not Found
                $response->status(404, 'Not Found', '1.1');
                break;
            case Router::NOT_FOUND_ACTION:
                // ... 405 Method Not Allowed
                $response->status(405, 'Method Not Allowed', '1.1');
                break;
            case Router::FOUND:
                //TODO::
                $controller = $routeInfo[1];
                $action = $routeInfo[2];
                $parameters = $routeInfo[3];

                $handler = new $controller($this->container);

                /**
                 * TODO:
                 */
                try {
                    $result = true;
                    $auth = new Auth();
                    $res = $auth($handler, $request, $response);

                    if ($res) {
                        $result = call_user_func_array(array($handler, $action), [$request, $response]);
//                        $response->setContent($result);
                    }
                } catch (ApplicationException $ex) {
                    $this->container->logger->error('ApplicationException', ['error' => $ex->getMessage()]);
                    $result = false;
                } catch (AjaxException $ex) {
                    $this->container->logger->error('AjaxException', ['error' => $ex->getMessage()]);
                    $response->setContent($ex->getMessage());
                    $result = false;
                } catch (\Exception $ex) {
                    $this->container->logger->error('Exception', ['error' => $ex->getMessage()]);
                    $result = false;
                } finally {
                }
                $response->response_sender();
                return ($result) ?: true;
        }
        $response->response_sender();
        return false;
    }

    public function process($request, $response)
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