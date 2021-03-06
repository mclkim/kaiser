<?php
declare(strict_types=1);

/**
 * @link      https://github.com/mclkim/kaiser
 * @copyright Copyright (p) myung chul kim
 * @license   MIT License
 */

//namespace Mcl\Kaiser;

/**
 * 프레임워크의 장점으로 생산성, 안정성, 보안을 들 수 있다.
 * 수퍼 개발자가 있어 열 명이 해야 할일을 혼자서 처리하고,
 * 해킹에 뚫릴 일이 전혀 없고 유지보수 하기도 편리한 자체 서비스 프레임워크를 직접 개발할 수 있다면,
 * 라라벨과 루멘과 같은 프레임워크를 쓸 필요 없다.
 *
 * 경험적으로 수퍼 개발자에 의존하는 서비스 운영은 절대 바람직하지 않다.
 * 수퍼 개발자가 자신의 직위를 악용하거나,
 * 아프거나 퇴사라도 하는 순간 서비스는 한 순간에 무너진다.
 *
 * 컴퓨터의 성능은 좋아졌고, 가격은 싸져서 스케일 업(scale-up)이 쉬워졌다.
 * 또, 쉽게 스케일 아웃(scale-out)할 수 있는 클라우드도 널렸다.
 * 개발자 한 명 채용하는 비용보다 컴퓨터 비용이 훨씬 싸다는 점을 잊지 말자.
 * 좋은 서버 쓰고 안정적인 코드를 빨리 개발하는 것이 더 현명하다.
 *
 * 반면 서비스가 궤도에 올라 사용자가 늘고 1ms와 1KB라도 쥐어 짜야 하는 상황이라면
 * 자체 프레임워크 개발을 고려해볼 만한다.
 * 물론 성능이 더 좋은 플랫폼으로 갈아 타는 방법도 있다.
 * 서비스를 살리기 위해 스택을 버려야지, 스택을 살리기 위해 서비스를 버리는 어리석음을 범하지 말라.
 *
 * 그러나, 라라벨이나 루멘은 범용 프레임워크이므로 최적화가 필요한 초대형 서비스에는 적합하지 않다는 생각이 든다.
 *
 * 출처:https://blog.appkr.kr
 */

/**
 * ---------------------------------------------------------------
 * ---------------------------------------------------------------
 */
// define('KAISER_START', microtime(true));

if (!function_exists('performance')) {
    function convert($size)
    {
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
    }

    function performance()
    {
        return [
            'Time: ' . (microtime(true) - KAISER_START),
            'Memory in use: ' . convert(memory_get_usage(true)),
            'Peak usage: ' . convert(memory_get_peak_usage()),
            'Memory limit: ' . ini_get('memory_limit'),
            // 'CPU(%): ' . sys_getloadavg()[0],
        ];
    }
}
/**
 * ---------------------------------------------------------------
 * 기본함수
 * ---------------------------------------------------------------
 */
if (!function_exists('get_client_ip_address')) {
    function get_client_ip_address($env)
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $env) === true) {
                foreach (explode(',', $env[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return null;
    }
}
/**
 * $args = ["database"=>"people", "user"=>"staff", "pass"=>"pass123", "host"=>"localhost"];
 *
 * // With PHP-like placeholders: the variable is embedded in a string "{$database}" but without the dollar sign
 * $format = <<<SQL
 * CREATE DATABASE IF NOT EXISTS {database};
 * GRANT ALL PRIVILEGES ON {database_name}.* TO '{user}'@'{host}';
 * SET PASSWORD = PASSWORD('{pass}');
 *
 * SQL;
 * echo p($format, $args);
 */
if (!function_exists('p')) {
    function p($format, array $args, $pattern = "/\{(\w+)\}/")
    {
        return preg_replace_callback($pattern, function ($matches) use ($args) {
            return @$args[$matches[1]] ?: $matches[0];
        }, $format);
    }
}

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
        return empty ($ret) ? $default : $ret;
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
if (!function_exists('array_sort_by_column')) {
    function array_sort_by_column(&$arr, $col, $dir = SORT_ASC)
    {
        $sort_col = array();
        foreach ($arr as $key => $row) {
            $sort_col[$key] = $row[$col];
        }
        array_multisort($sort_col, $dir, $arr);
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
if (!function_exists('get_name_space')) {
    function get_name_space($classname)
    {
//        if ($pos = strrpos($classname, '\\')) return substr($classname, 0, $pos);
//        return $pos;
        if ($pos = strrpos($classname, '\\')) return str_replace("\\", "/", $classname);
        return $classname;
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
/**
 * ---------------------------------------------------------------
 * 정규분포
 * ---------------------------------------------------------------
 */
// https://stackoverflow.com/questions/7224437/php-statistics-z-score-normal-distributions
// http://web.stanford.edu/class/cs109/demos/cdf.html
if (!function_exists('cumnormdist')) {
    function cumnormdist($x)
    {
        $b1 = 0.319381530;
        $b2 = -0.356563782;
        $b3 = 1.781477937;
        $b4 = -1.821255978;
        $b5 = 1.330274429;
        $p = 0.2316419;
        $c = 0.39894228;

        if ($x >= 0.0) {
            $t = 1.0 / (1.0 + $p * $x);
            return (1.0 - $c * exp(-$x * $x / 2.0) * $t * ($t * ($t * ($t * ($t * $b5 + $b4) + $b3) + $b2) + $b1));
        } else {
            $t = 1.0 / (1.0 - $p * $x);
            return ($c * exp(-$x * $x / 2.0) * $t * ($t * ($t * ($t * ($t * $b5 + $b4) + $b3) + $b2) + $b1));
        }
    }
}
if (!function_exists('z')) {
    /* Z Scores */
    function z($var, $mean, $std)
    {
        return ($var - $mean) / $std;
    }
}
if (!function_exists('sd_square')) {
    // Function to calculate square of value - mean
    function sd_square($x, $mean)
    {
        return pow($x - $mean, 2);
    }
}
if (!function_exists('sd')) {
    // Function to calculate standard deviation (uses sd_square)
    function sd($array)
    {
        // square root of sum of squares devided by N-1
        return sqrt(array_sum(array_map("sd_square", $array, array_fill(0, count($array), (array_sum($array) / count($array))))) / (count($array) - 1));
    }
}