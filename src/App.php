<?php
/**
 * Created by PhpStorm.
 * User: 김준수
 * Date: 2018-07-11
 * Time: 오후 1:34
 */

namespace Mcl\Kaiser;

<<<<<<< HEAD
use Psr\Container\ContainerInterface;

class App //extends Controller
{
    const VERSION = '2018-07-15';

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
=======
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
>>>>>>> 26aa3e402fdbd25eed47c460755f00c907c00a92
    }

    public function run($directory = [], $silent = false)
    {
<<<<<<< HEAD
        if ($this->container->has('session')) {
            $this->container->get('session');

            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }
        }

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

                    $auth = new Auth();
                    $res = $auth($handler, $request, $response);

                    if ($res) {
                        ob_start();
                        $result = call_user_func_array(array($handler, $action), [$request, $response]);
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
                    $output = ob_get_clean();
                    $response->setContent($output);
                }
                $response->response_sender();
                return ($result) ?: true;
        }
        $response->response_sender();
        return false;
=======
//        var_dump($_SERVER);
//        phpinfo();
//        exit;
        $container = $this->getContainer();
        $request = $container->get('request');
        $uri = $request->getUri();
        $path = $uri->getPath();

        $router = new Router();
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
                $handler = new $controller($container);

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
        //TODO::How to Direct Routing basePath
        $reqServer = $_SERVER;
        $reqServer['SCRIPT_NAME'] = "/{$_SERVER['SCRIPT_NAME']}";
        /**
         * Add Overide on Application initial
         * set container request with new value of \Slim\Http\Request
         * with new values
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
>>>>>>> 26aa3e402fdbd25eed47c460755f00c907c00a92
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