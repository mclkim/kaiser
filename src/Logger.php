<?php

namespace Mcl\Kaiser;

use Katzgrau\KLogger\Logger as KLogger;

/**
 * composer require katzgrau/klogger "^1.2"
 * Class Logger
 * @package Mcl\Kaiser
 */
class Logger extends KLogger
{
    public function log($level, $message, array $context = array())
    {
        if ($this->logLevels [$this->logLevelThreshold] < $this->logLevels [$level]) {
            return;
        }
        list ($file, $line, $func, $class) = $this->_getBacktraceVars(2);

        // TODO::
        $message = is_array($message) ? var_export($message, true) : $message;
        $message = $this->_formatMessage($level, $message, $context, $file, $line, $func, $class);
        $this->write($message);
    }

    private function getTimestamp()
    {
        $originalTime = microtime(true);
        $micro = sprintf("%06d", ($originalTime - floor($originalTime)) * 1000000);
        $date = new \DateTime (date('Y-m-d H:i:s.' . $micro, $originalTime));

        return $date->format($this->options ['dateFormat']);
    }

    private function _formatMessage($level, $message, $context, $file, $line, $func, $class)
    {
        $level = strtoupper($level);
        if (!empty ($context)) {
            $message .= PHP_EOL . $this->indent($this->contextToString($context));
        }
        return "[{$this->getTimestamp()}] [{$level}] [{$file} {$line} {$func}] {$message}" . PHP_EOL;
    }

    private function _getBacktraceVars($depth)
    {
        // From http://pear.php.net/package-info.php?package=Log
        // modified slightly to work here

        /* Start by generating a backtrace from the current call (here). */
        $bt = debug_backtrace();

        /* Store some handy shortcuts to our previous frames. */
        $bt0 = isset ($bt [$depth]) ? $bt [$depth] : null;
        $bt1 = isset ($bt [$depth + 1]) ? $bt [$depth + 1] : null;

        /*
         * If we were ultimately invoked by the composite handler, we need to increase our depth one additional level to compensate.
         */
        $class = isset ($bt1 ['class']) ? $bt1 ['class'] : null;
        if ($class !== null && strcasecmp($class, 'Log_composite') == 0) {
            $depth++;
            $bt0 = isset ($bt [$depth]) ? $bt [$depth] : null;
            $bt1 = isset ($bt [$depth + 1]) ? $bt [$depth + 1] : null;
            $class = isset ($bt1 ['class']) ? $bt1 ['class'] : null;
        }

        /*
         * We're interested in the frame which invoked the log() function, so we need to walk back some number of frames into the backtrace. The $depth parameter tells us where to start looking. We go one step further back to find the name of the encapsulating function from which log() was called.
         */
        $file = isset ($bt0) ? $bt0 ['file'] : null;
        $line = isset ($bt0) ? $bt0 ['line'] : 0;
        $func = isset ($bt1) ? $bt1 ['function'] : null;

        /*
         * However, if log() was called from one of our "shortcut" functions, we're going to need to go back an additional step.
         */
        if (in_array($func, array(
            'emerg',
            'alert',
            'crit',
            'err',
            'warning',
            'notice',
            'info',
            'debug'
        ))) {
            $bt2 = isset ($bt [$depth + 2]) ? $bt [$depth + 2] : null;

            $file = is_array($bt1) ? $bt1 ['file'] : null;
            $line = is_array($bt1) ? $bt1 ['line'] : 0;
            $func = is_array($bt2) ? $bt2 ['function'] : null;
            $class = isset ($bt2 ['class']) ? $bt2 ['class'] : null;
        }

        /*
         * If we couldn't extract a function name (perhaps because we were executed from the "main" context), provide a default value.
         */
        if ($func === null) {
            $func = '(none)';
        }

        /* Return a 4-tuple containing (file, line, function, class). */
        return array(
            $file,
            $line,
            $func,
            $class
        );
    }
}