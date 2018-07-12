<?php

namespace Mcl\Kaiser;

class Router
{
    const NOT_FOUND = 0;
    const FOUND = 1;
    const NOT_FOUND_ACTION = 2;
    const METHOD_NOT_ALLOWED = 3;

    private $AppDir;
    private $container;

    public function dispatch($path)
    {
        $route = new Route();
        $route->getRoute($path);

        $controller = $route->getController();
        $action = $route->getAction();
        $parameters = $route->getParameters();

        //TODO::
        return $this->findController($controller, $action, $parameters, $this->getAppDir());
    }

    protected function findController($controller, $action, $parameters, $inPath)
    {
        $directory = is_array($inPath) ? $inPath : array($inPath);

        $classname = self::normalizeClassName($controller);
        /**
         * TODO::UNIX 시스템에서 파일이름의 대소문자 구별한다.(2016-12-02)
         */
        if (!class_exists($classname)) {
            foreach ($directory as $inPath) {
                $controllerFile = $inPath . $controller . '.php';
                $controllerFile = realpath($controllerFile);
                if (file_exists($controllerFile)) {
                    include_once($controllerFile);
                    break;
                }
            }
        }

        if (!class_exists($classname)) {
            return [self::NOT_FOUND, $classname, $action, $parameters];
        }

        //TODO::
        $handler = array(new $classname($this->container), $action);
        if (is_callable($handler)) {
            return [self::FOUND, $classname, $action, $parameters];
        }

        return [self::NOT_FOUND_ACTION, $classname, $action, $parameters];
    }

    public static function normalizeClassName($name)
    {
        $name = str_replace('/', '\\', $name);

        if (is_object($name))
            $name = get_class($name);

        $name = '\\' . ltrim($name, '\\');
        return $name;
    }

    function getAppDir()
    {
        return $this->AppDir;
    }

    function setAppDir($directory = [])
    {
        $this->AppDir = $directory;
    }

    function getContainer()
    {
        return $this->container;
    }

    function setContainer($container)
    {
        $this->container = $container;
    }
}