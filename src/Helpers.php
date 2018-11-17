<?php

//namespace Mcl\Kaiser;
/**
 * 프레임워크의 장점으로 생산성, 안정성, 보안을 들 수 있다.
 * 수퍼 개발자가 있어 열 명이 해야 할일을 혼자서 처리하고, 해킹에 뚫릴 일이 전혀 없고 유지보수 하기도 편리한 자체 서비스 프레임워크를 직접 개발할 수 있다면, 라라벨과 루멘과 같은 프레임워크를 쓸 필요 없다.
 *
 * 경험적으로 수퍼 개발자에 의존하는 서비스 운영은 절대 바람직하지 않다.
 * 수퍼 개발자가 자신의 지위를 악용하거나, 아프거나 퇴사라도 하는 순간 서비스는 한 순간에 무너진다.
 *
 * 컴퓨터의 성능은 좋아졌고, 가격은 싸져서 스케일 업(scale-up)이 쉬워졌다.
 * 또, 쉽게 스케일 아웃(scale-out)할 수 있는 클라우드도 널렸다.
 * 개발자 한 명 채용하는 비용보다 컴퓨터 비용이 훨씬 싸다는 점을 잊지 말자.
 * 좋은 서버 쓰고 안정적인 코드를 빨리 개발하는 것이 더 현명하다.
 *
 * 반면 서비스가 궤도에 올라 사용자가 늘고 1ms와 1KB라도 쥐어 짜야 하는 상황이라면 자체 프레임워크 개발을 고려해볼 만한다.
 * 물론 성능이 더 좋은 플랫폼으로 갈아 타는 방법도 있다.
 * 서비스를 살리기 위해 스택을 버려야지, 스택을 살리기 위해 서비스를 버리는 어리석음을 범하지 말라.
 *
 * 라라벨이나 루멘은 범용 프레임워크이므로 최적화가 필요한 초대형 서비스에는 적합하지 않다는 생각이 든다.
 *
 * 출처:https://blog.appkr.kr
 */

/**
 * ---------------------------------------------------------------
 * ---------------------------------------------------------------
 */
// define('KAISER_START', microtime(true));

if (!function_exists('performance')) {
    function performance()
    {
        return [
            '처리시간(ms): ' . (microtime(true) - KAISER_START),
            '메모리(MB): ' . memory_get_usage() / 1000000,
            '메모리(MB): ' . memory_get_usage() / 1048576,
            // 'CPU(%): ' . sys_getloadavg()[0],
        ];
    }
}
/**
 * ---------------------------------------------------------------
 * 기본함수
 * ---------------------------------------------------------------
 */
if (!function_exists('if_exists')) {
    function if_exists($array, $key, $default = null)
    {
        if (is_array($array) == false) {
            return $default;
        }
        return array_key_exists($key, $array) ? $array [$key] : $default;
    }
}
if (!function_exists('if_empty')) {
    function if_empty($array, $key, $default = null)
    {
        $ret = if_exists($array, $key, $default);
        return !empty ($ret) ? $ret : $default;
    }
}
if (!function_exists('if_digit')) {
    function if_digit($array, $key, $default = null)
    {
        $ret = if_exists($array, $key, $default);
        return is_numeric($ret) ? $ret : $default;
    }
}
if (!function_exists('search')) {
    function search($array, $key, $value)
    {
        $results = array();

        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value)
                $results[] = $array;

            foreach ($array as $subarray)
                $results = array_merge($results, search($subarray, $key, $value));
        }

        return $results;
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