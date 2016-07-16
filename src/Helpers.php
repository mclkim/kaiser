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
