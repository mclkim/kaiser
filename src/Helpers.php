<?php

//namespace Mcl\Kaiser;

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
if (!function_exists('file_size')) {
    function file_size($size)
    {
        $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
        return $size ? round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';
    }
}
if (!function_exists('bytesize')) {
    function bytesize($bytes, $decimals = 0)
    {
        if (empty ($bytes) || $bytes < 0)
            return 0;

        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', "EB", "ZB", "YB");

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
if (!function_exists('base_url')) {
    function base_url($atRoot = FALSE, $atCore = FALSE, $parse = FALSE)
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $http = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
            $hostname = $_SERVER['HTTP_HOST'];
            $dir = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

            $core = preg_split('@/@', str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(dirname(__FILE__))), NULL, PREG_SPLIT_NO_EMPTY);
            $core = $core[0];

            $tmplt = $atRoot ? ($atCore ? "%s://%s/%s/" : "%s://%s/") : ($atCore ? "%s://%s/%s/" : "%s://%s%s");
            $end = $atRoot ? ($atCore ? $core : $hostname) : ($atCore ? $core : $dir);
            $base_url = sprintf($tmplt, $http, $hostname, $end);
        } else $base_url = 'http://localhost/';

        if ($parse) {
            $base_url = parse_url($base_url);
            if (isset($base_url['path'])) if ($base_url['path'] == '/') $base_url['path'] = '';
        }

        return $base_url;
    }
}
