<?php

namespace Kaiser;

class Singleton
{
    // If your model support higher than PHP 5.3, you do not implement getInstance method.
    // However, models for prior to PHP 5.3 should have getInstance method.
    // See below (<5.3 Compatible singleton implementation)
    private static $instances;

    public function __construct()
    {
        $c = get_class_name(__CLASS__);
        if (isset (self::$instances [$c])) {
            throw new \Exception ('You can not create more than one copy of a singleton.');
        } else {
            self::$instances [$c] = $this;
        }
    }

    public static function _getInstance($p = null)
    {
        $c = get_called_class();
        if (!isset (self::$instances [$c])) {
            $args = func_get_args();
            $reflection_object = new \ReflectionClass ($c);
            self::$instances [$c] = $reflection_object->newInstanceArgs($args);
        }
        return self::$instances [$c];
    }

    public static function getInstance()
    {
        return self::_getInstance();
    }

    public function __clone()
    {
        throw new \Exception ('You can not clone a singleton.');
    }
}
