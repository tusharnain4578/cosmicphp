<?php

namespace Core\Console;

class Console
{
    public static function error(string $message)
    {
        self::output("\033[0;31m{$message}\033[0m");
    }

    public static function info(string $message)
    {
        self::output("\033[0;34m{$message}\033[0m");
    }

    public static function success(string $message)
    {
        self::output("\033[0;32m{$message}\033[0m");
    }

    public static function warning(string $message)
    {
        self::output("\033[0;33m{$message}\033[0m");
    }

    protected static function output(string $message)
    {
        echo $message . PHP_EOL;
    }
}
