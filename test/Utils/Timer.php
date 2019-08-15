<?php

namespace Test\Utils;

class Timer
{
    private static $timestamp = null;

    private static function output($timestamp)
    {
        $elapsed = round($timestamp - self::$timestamp, 4);
        echo "Time elapsed:\t{$elapsed}s\n\n";
    }

    public static function start(?string $message = null)
    {
        if (!is_null(self::$timestamp)) {
            self::finish();
        }
        echo ($message ?? "Started timer...") . "\n";
        self::$timestamp = microtime(true);
    }

    public static function finish()
    {
        self::output(microtime(true));
        self::$timestamp = null;
    }
}