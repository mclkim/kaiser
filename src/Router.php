<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2019-12-13
 * Time: 오전 7:36
 */

namespace Mcl\Kaiser;

use Slim\Psr7\Factory\ServerRequestFactory;

class Router
{
    const NOT_FOUND = 0;
    const FOUND = 1;
    const NOT_FOUND_ACTION = 2;
    const METHOD_NOT_ALLOWED = 3;

    protected $map;
    protected $path;

    public function __construct($map = [], $path = null)
    {
        if (!isset($path)) {
            $request = ServerRequestFactory::createFromGlobals();
            $path = $request->getUri()->getPath();
        }

        $this->path = $path;
        $this->map = is_array($map) ? $map : ["" => $map];
    }

    public function dispatch()
    {
        $route = new Route($this->path);
        $route->getRoute();

        $controller = $route->getController();
        $action = $route->getAction();
        $parameters = $route->getParameters();
        $map = $this->getAppMap();

        //TODO::
        return $this->findController($controller, $action, $parameters, $map);
    }

    function getPath()
    {
        return $this->path;
    }

    function getAppMap()
    {
        return $this->map;
    }

    function setAppMap($map = [])
    {
        $this->map = is_array($map) ? $map : ["" => $map];
    }

    protected function findController($controller, $action, $parameters, $map)
    {
        foreach ($map as $prefix => $path) {
            $classname = trim($prefix, '\\') . '/' . trim($controller, '/');
            $classname = self::normalizeClassName($classname);
            /**
             * TODO::UNIX 시스템에서 파일이름의 대소문자 구별한다.(2016-12-02)
             */
            if (!class_exists($classname)) {
                $controllerFile = rtrim($path, '/') . '/' . trim($controller, '/') . '.php';
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

        if (!is_callable([new $classname, $action])) {
            return [self::NOT_FOUND_ACTION, $classname, $action, $parameters];
        }

        return [self::FOUND, $classname, $action, $parameters];
    }

    public static function normalizeClassName($name)
    {
        $name = str_replace('/', '\\', $name);

        if (is_object($name))
            $name = get_class($name);

        $name = '\\' . ltrim($name, '\\');
        return $name;
    }
}