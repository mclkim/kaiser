<?php

namespace Kaiser;

use Kaiser\Request;

class Router
{
    const NOT_FOUND = 0;
    const FOUND = 1;
    const METHOD_NOT_ALLOWED = 2;

    private $AppDirectory;
    private $basePath;
    private $Url;
    private $Method;
    private $parameters;

    public function __construct()
    {

    }

    function setAppDir($directory = [])
    {
        $this->AppDirectory = $directory;
    }

    function getAppDir()
    {
        return $this->AppDirectory;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    public function dispatch($container)
    {
        $req = new Request();
        $this->Url = $req->url(PHP_URL_QUERY);
        $this->Method = $req->method();
        $this->parameters = $req->get();

        $route = new Route(array('methods' => ['GET', 'POST']));
        $route->getRoute($this->Url);

        $controller = $route->getController();
        $action = $route->getAction();
        $parameters = $route->getParameters();

        //TODO::
        $callable = $this->findController($container, $controller, $action, $this->getAppDir());

        switch ($callable) {
            case Router::NOT_FOUND:
                // ... 404 Not Found
                break;
            case Router::METHOD_NOT_ALLOWED:
                // ... 405 Method Not Allowed
                break;
            case Router::FOUND:
                $controller = self::normalizeClassName($controller);
                $instance = [
                    new $controller ($container),
                    $action
                ];
                $result = call_user_func_array($instance, $parameters);
        }
        return $this;
    }

    public function match($requestMethod, $requestUrl)
    {
    }

    public static function normalizeClassName($name)
    {
        $name = str_replace('/', '\\', $name);

        if (is_object($name))
            $name = get_class($name);

        $name = '\\' . ltrim($name, '\\');
        return $name;
    }

    protected function findController($container, $controller, $action, $inPath)
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
            return Router::NOT_FOUND;
        }

        //TODO::
        $instance = [
            new $controller ($container),
            $action
        ];

        if (is_callable($instance)) {
            return Router::FOUND;
        }

        return Router::METHOD_NOT_ALLOWED;
    }
}