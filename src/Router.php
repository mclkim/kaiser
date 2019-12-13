<?php
/**
 * Created by PhpStorm.
 * User: 김명철
 * Date: 2019-12-13
 * Time: 오전 7:36
 */

namespace Mcl\Kaiser;

use Slim\Psr7\Factory\ServerRequestFactory;

class Router extends Route
{
    const NOT_FOUND = 0;
    const FOUND = 1;
    const NOT_FOUND_ACTION = 2;
    const METHOD_NOT_ALLOWED = 3;

    protected $map;
//TODO::mclkim
//    protected $path;

    public function __construct($path = null)
    {
        if (!isset($path)) {
            $request = ServerRequestFactory::createFromGlobals();
            $path = $request->getUri()->getPath();
        }

        parent::__construct($path);
//TODO::mclkim
//        $this->path = $path;
    }

    public function dispatch()
    {
        $this->getRoute();

        $controller = $this->getController();
        $action = $this->getAction();
        $parameters = $this->getParameters();
        $map = $this->getAppMap();

        //TODO::
        return $this->findController($controller, $action, $parameters, $map);
    }

    function getAppMap()
    {
        return $this->map;
    }

//    function getPath()
//    {
//        return $this->path;
//    }

    function setAppMap($map = [])
    {
        $this->map = is_array($map) ? $map : ["" => $map];;
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