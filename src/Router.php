<?php

namespace Kaiser;

use Kaiser\Request;

class Router
{
    const NOT_FOUND = 0;
    const FOUND = 1;
    const NOT_FOUND_ACTION = 2;
    const METHOD_NOT_ALLOWED = 3;

    private $AppDir;
    private $url;
    private $method;

//    private $parameters;

    function __construct()
    {
        $req = new Request();
        $this->url = $req->url(PHP_URL_QUERY);
        $this->method = $req->method();
        if ($handler = $req->header('X-October-Request-Handler')) {
            $this->url = $handler;
        } else if ($handler = $req->header('X-Request-Handler')) {
            $this->url = $handler;
        }
    }

    function setAppDir($directory = [])
    {
        $this->AppDir = $directory;
    }

    function getAppDir()
    {
        return $this->AppDir;
    }

    public function dispatch(array $config)
    {
        $route = new Route($config);
        $route->getRoute($this->url);

        $controller = $route->getController();
        $action = $route->getAction();
        $parameters = $route->getParameters();

        //TODO::
        return $this->findController($controller, $action, $parameters, $this->getAppDir());
    }

    public static function normalizeClassName($name)
    {
        $name = str_replace('/', '\\', $name);

        if (is_object($name))
            $name = get_class($name);

        $name = '\\' . ltrim($name, '\\');
        return $name;
    }

    protected function findController($controller, $action, $parameters, $inPath)
    {
        $directory = is_array($inPath) ? $inPath : array(
            $inPath
        );

        /**
         * Workaround: Composer does not support case insensitivity.
         * TODO::2016-12-02 unix 시스템에서 파일이름의 대소문자 구별한다.
         */
        if (!class_exists($controller)) {
            $controller = self::normalizeClassName($controller);
            foreach ($directory as $inPath) {
                $controllerFile = $inPath . str_replace('\\', '/', $controller) . '.php';
                if (file_exists($controllerFile)) {
                    include_once($controllerFile);
                    break;
                }
            }
        }

        if (!class_exists($controller)) {
            return [self::NOT_FOUND, $controller, $action, $parameters];
        }

        //TODO::
        $handler = array(new $controller, $action);

        if (is_callable($handler)) {
            return [self::FOUND, $controller, $action, $parameters];
        }

        return [self::NOT_FOUND_ACTION, $controller, $action, $parameters];
    }
}