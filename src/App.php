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
    var $path;
    var $class;
    var $action;
    var $controller;
    var $parameters = array();
    private $container;
    private $url;

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

    function run2($app = '\App')
    {
        $request = $this->container->get('request');
        $response = $this->container->get('response');
        $this->url = $request->url(PHP_URL_PATH);

        $x = self::__URIPath($this->url);

        $this->path = empty($x['dirname']) ? '' : $x['dirname'];
        $this->class = empty($x['filename']) ? 'index' : $x['filename'];
        $this->action = empty($x['extension']) ? 'execute' : $x['extension'];
        $this->controller = $app . rtrim($this->path, '/') . '/' . $this->class;
        $this->controller = str_replace('/', '\\', $this->controller);

        $dispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) {
            $callable = new $this->controller($this->container);
            $r->addRoute($callable->methods(), $this->url, $callable);
        });

        // Fetch method and URI from somewhere
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

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

    private function __URIPath($url)
    {
        preg_match('%^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^\.\\\\/]+?)|))[\\\\/\.]*$%im', $url, $m);
        return array(
            'dirname' => isset ($m [1]) ? $m [1] : '',
            'basename' => isset ($m [2]) ? $m [2] : '',
            'extension' => isset ($m [5]) ? $m [5] : '',
            'filename' => isset ($m [3]) ? $m [3] : ''
        );
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