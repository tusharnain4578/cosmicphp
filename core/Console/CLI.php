<?php

namespace Core\Console;

use Core\Database\Migration;
use Core\Services\Cache;

class CLI
{
    private array $args = [];
    private ?string $param;
    public function __construct($args = [])
    {
        $this->args = get_commandLine_arg();
        $this->param = trim($this->args[0] ?? '');

        if (!$this->param) {
            Console::error(message: "Parameter Required!");
            exit;
        }
    }
    public function run()
    {
        if (str_starts_with($this->param, 'migrate')) {
            Migration::handleCommand(args: $this->args);
        } else if (str_starts_with($this->param, 'cache')) {
            Cache::handleCommand(args: $this->args);
        } else {
            self::invalidParamMessage();
        }
    }

    public static function invalidParamMessage($message = 'Invalid Parameter!', bool $exit = true)
    {
        Console::error(message: $message);
        if ($exit)
            exit;
    }
}