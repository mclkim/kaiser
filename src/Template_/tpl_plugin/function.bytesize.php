<?php
/**
 * ---------------------------------------------------------------
 * Library 함수
 *
 * [].파일 사이즈 포멧하기(api)
 * [].내가 생각해도 너무 잘 잘 만드것 같은데...
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
        if (empty ($bytes) || $bytes < 0) return 0;

        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', "EB", "ZB", "YB");

        $unit = floor(log($bytes, 2) / 10);
        if ($unit == 0) $decimals = 0;
        return number_format($bytes / pow(1024, $unit), $decimals) . ' ' . $units [$unit];
    }
}
if (!function_exists('format_bytesize')) {
    function format_bytesize($bytes, $decimals = 0, $force_unit = true, $dec_char = '.', $thousands_char = ',')
    {
        if (empty ($bytes) || $bytes < 0) return 0;

        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', "EB", "ZB", "YB");

        if ($force_unit === false)
            $unit = floor(log($bytes, 2) / 10);
        else
            $unit = $force_unit;

        if ($unit == 0) $decimals = 0;
        return number_format($bytes / pow(1024, $unit), $decimals, $dec_char, $thousands_char) . ' ' . $units [$unit];
    }
}
?>
