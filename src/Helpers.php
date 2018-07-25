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
if (!function_exists('if_digit')) {
    function if_digit($array, $key, $def = null)
    {
        $ret = if_exists($array, $key, $def);
        return is_numeric($ret) ? $ret : $def;
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
if (!function_exists('byte_size')) {
    function byte_size($bytes, $decimals = 0)
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
if (!function_exists('starts_with')) {
    function starts_with($haystack, $needle)
    {
        return ((FALSE !== strpos($haystack, $needle)) &&
            (0 == strpos($haystack, $needle)));
    }
}
if (!function_exists('ends_with')) {
    function ends_with($haystack, $needle)
    {
        return strrpos($haystack, $needle) === strlen($haystack) - strlen($needle);
    }
}