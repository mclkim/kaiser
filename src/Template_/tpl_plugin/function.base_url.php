<?php
/**
 * Created by PhpStorm.
 * User: 김준수
 * Date: 2018-07-19
 * Time: 오후 12:40
 *
 * //  url like: http://stackoverflow.com/questions/2820723/how-to-get-base-url-with-php
 * echo base_url();    //  will produce something like: http://stackoverflow.com/questions/2820723/
 * echo base_url(TRUE);    //  will produce something like: http://stackoverflow.com/
 * echo base_url(TRUE, TRUE); || echo base_url(NULL, TRUE);    //  will produce something like: http://stackoverflow.com/questions/
 * echo base_url(NULL, NULL, TRUE); //  and finally
 * //  will produce something like:
 * //      array(3) {
 * //          ["scheme"]=>
 * //          string(4) "http"
 * //          ["host"]=>
 * //          string(12) "stackoverflow.com"
 * //          ["path"]=>
 * //          string(35) "/questions/2820723/"
 * //      }
 */
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
