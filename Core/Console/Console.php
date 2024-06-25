<?php

namespace Core\Console;

class Console
{
    private static $inputHandle;

    public static function init()
    {
        self::$inputHandle = fopen("php://stdin", "r");
    }

    public static function error(string $message, bool $EOL = true)
    {
        self::output("\033[0;31m{$message}\033[0m", $EOL);
    }

    public static function info(string $message, bool $EOL = true)
    {
        self::output("\033[0;34m{$message}\033[0m", $EOL);
    }

    public static function success(string $message, bool $EOL = true)
    {
        self::output("\033[0;32m{$message}\033[0m", $EOL);
    }

    public static function warning(string $message, bool $EOL = true)
    {
        self::output("\033[0;33m{$message}\033[0m", $EOL);
    }

    private static function output(string $message, bool $EOL = true)
    {
        echo $message . ($EOL ? PHP_EOL : '');
    }



    /**
     * Used to take input from CLI
     * $colorType can be ['info', 'success', 'error', 'warning']
     */
    public static function ask(string $question, string $colorType = 'info', bool $EOL = false): null|string
    {
        echo self::$colorType($question, $EOL);
        $input = fgets(self::$inputHandle);
        $input = trim($input);
        return empty($input) ? null : $input;
    }

    public static function askInLoop(string $question, string $errorMessage = '', $colorType = 'info', bool $EOL = false): null|string
    {
        $input = null;
        while (is_null($input)) {
            $input = Console::ask($question);
            if (is_null($input) && !empty($errorMessage))
                Console::error($errorMessage);
        }
        return $input;
    }


    public static function close()
    {
        fclose(self::$inputHandle);
    }
}
