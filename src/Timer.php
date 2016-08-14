<?php

namespace Kaiser;

final class Timestamp
{
    static function format($format = '%c', $time = null)
    {
        if (isset ($time))
            return strftime(_t($format), $time);
        else
            return strftime(_t($format));
    }

    static function formatGMT($format = '%c', $time = null)
    {
        if (isset ($time))
            return gmstrftime(_t($format), $time);
        else
            return gmstrftime(_t($format));
    }

    static function format2($time)
    {
        if (date('Ymd', $time) == date('Ymd'))
            return strftime(_t('%H:%M'), $time);
        else if (date('Y', $time) == date('Y', time()))
            return strftime(_t('%m/%d'), $time);
        else
            return strftime(_t('%Y'), $time);
    }

    static function format3($time)
    {
        if (date('Ymd', $time) == date('Ymd'))
            return strftime(_t('%H:%M:%S'), $time);
        else
            return strftime(_t('%Y/%m/%d'), $time);
    }

    static function format5($time = null)
    {
        return (isset ($time) ? strftime(_t('%Y/%m/%d %H:%M'), $time) : strftime(_t('%Y/%m/%d %H:%M')));
    }

    static function formatDate($time = null)
    {
        return (isset ($time) ? strftime(_t('%Y/%m/%d'), $time) : strftime(_t('%Y/%m/%d')));
    }

    static function formatDate2($time = null)
    {
        return (isset ($time) ? strftime(_t('%Y/%m'), $time) : strftime(_t('%Y/%m')));
    }

    static function formatTime($time = null)
    {
        return (isset ($time) ? strftime(_t('%H:%M:%S'), $time) : strftime(_t('%H:%M:%S')));
    }

    static function get($format = 'YmdHis', $time = null)
    {
        return (isset ($time) ? date($format, $time) : date($format));
    }

    static function getGMT($format = 'YmdHis', $time = null)
    {
        return (isset ($time) ? gmdate($format, $time) : gmdate($format));
    }

    static function getDate($time = null)
    {
        return (isset ($time) ? date('Ymd', $time) : date('Ymd'));
    }

    static function getYearMonth($time = null)
    {
        return (isset ($time) ? date('Ym', $time) : date('Ym'));
    }

    static function getYear($time = null)
    {
        return (isset ($time) ? date('Y', $time) : date('Y'));
    }

    static function getTime($time = null)
    {
        return (isset ($time) ? date('His', $time) : date('His'));
    }

    static function getRFC1123($time = null)
    {
        return (isset ($time) ? date('r', $time) : date('r'));
    }

    static function getRFC1123GMT($time = null)
    {
        return (isset ($time) ? gmdate('D, d M Y H:i:s \G\M\T', $time) : gmdate('D, d M Y H:i:s \G\M\T'));
    }

    static function getRFC1036($time = null)
    {
        return ((isset ($time) ? date('l, d-M-Y H:i:s ', $time) : date('l, d-M-Y H:i:s ')) . Timezone::getRFC822());
    }

    static function getISO8601($time = null)
    {
        return ((isset ($time) ? date('Y-m-d\TH:i:s', $time) : date('Y-m-d\TH:i:s')) . Timezone::getISO8601());
    }

    static function getUNIXtime($time = null)
    {
        return intval(isset ($time) ? date('U', $time) : date('U'));
    }

    static function getHumanReadable($time = null, $from = null)
    {
        if (is_null($from))
            $deviation = Timestamp::getUNIXtime() - Timestamp::getUNIXtime($time);
        else
            $deviation = Timestamp::getUNIXtime($from) - Timestamp::getUNIXtime($time);

        if ($deviation > 0) { // Past.
            if ($deviation < 60) {
                return _f('%1초 전', $deviation);
            } else if ($deviation < 3600) {
                return _f('%1분 전', intval($deviation / 60));
            } else if ($deviation < 86400) {
                return _f('%1시간 전', intval($deviation / 3600));
            } else if ($deviation < 604800) {
                return _f('%1일 전', intval($deviation / 86400));
            } else {
                return _f('%1주 전', intval($deviation / 604800));
            }
        } else {
            $deviation = abs($deviation);
            if ($deviation < 60) {
                return _f('%1초 후', $deviation);
            } else if ($deviation < 3600) {
                return _f('%1분 후', intval($deviation / 60));
            } else if ($deviation < 86400) {
                return _f('%1시간 후', intval($deviation / 3600));
            } else if ($deviation < 604800) {
                return _f('%1일 후', intval($deviation / 86400));
            } else {
                return _f('%1주 후', intval($deviation / 604800));
            }
        }
    }
}

final class Timer
{
    /**
     * Original code is written by Crizin (crizin@gmail.com)
     */
    private $start, $stop;

    function __construct()
    {
        $this->start();
    }

    public function start()
    {
        $this->start = $this->getMicroTime();
    }

    public function pause()
    {
        $this->stop = $this->getMicroTime();
    }

    public function resume()
    {
        $this->start += $this->getMicroTime() - $this->stop;
        $this->stop = 0;
    }

    public function fetch($decimalPlaces = 3)
    {
        return sprintf('%.3f', round(($this->getMicrotime() - $this->start), $decimalPlaces));
    }

    public static function getMicroTime()
    {
        list ($usec, $sec) = explode(' ', microtime());
        return ( float )$usec + ( float )$sec;
    }
}