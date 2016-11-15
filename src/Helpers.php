<?php
use Kaiser\App;

if (!function_exists('app')) {
    /**
     * Get the available container instance.
     */
    function app($make = null, $parameters = [])
    {
        if (is_null($make))
            return App::getInstance();

        return App::getInstance()->getContainer()->get($make);
    }
}
if (!function_exists('logger')) {
    /**
     * Log a debug message to the logs.
     */
    function logger($message = null, array $context = array())
    {
        if (is_null($message))
            return app('logger');

        return app('logger')->debug($message, $context);
    }
}
if (!function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     */
    function base_path($path = '')
    {
        return app()->getBasePath() . ($path ? '/' . $path : $path);
    }
}
/**
 * ---------------------------------------------------------------
 * 기본함수
 * ---------------------------------------------------------------
 */
if (!function_exists('if_exists')) {
    function if_exists($array, $key, $def = null)
    {
        if (is_array($array) == false) {
            return $def;
        }
        return array_key_exists($key, $array) ? $array [$key] : $def;
    }
}
if (!function_exists('if_empty')) {
    function if_empty($array, $key, $def = null)
    {
        $ret = if_exists($array, $key, $def);
        return !empty ($ret) ? $ret : $def;
    }
}
/**
 * ---------------------------------------------------------------
 * 기타함수
 * ---------------------------------------------------------------
 */
if (!function_exists('bytesize')) {
    function bytesize($bytes, $decimals = 0)
    {
        if (empty ($bytes) || $bytes < 0)
            return 0;

        $units = array(
            'B',
            'KB',
            'MB',
            'GB',
            'TB',
            'PB'
        );

        $unit = floor(log($bytes, 2) / 10);
        if ($unit == 0)
            $decimals = 0;
        return number_format($bytes / pow(1024, $unit), $decimals) . ' ' . $units [$unit];
    }
}
if (!function_exists('get_class_name')) {
    function get_class_name($classname)
    {
//        if ($pos = strrpos($classname, '\\')) return substr($classname, $pos + 1);
//        return $pos;
        $path = explode('\\', $classname);
        return array_pop($path);
    }
}
if (!function_exists('get_digit')) {
    function get_digit($str)
    {
        return $num = preg_replace("/[^0-9]*/s", "", $str);
    }
}

