<?php

namespace Framework\Console;

use Framework\Database\Migration;

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
        switch ($this->param) {

            case 'migrate': {
                Migration::run();
                exit;
            }

        }

        Console::error(message: 'Invalid Parameter!');
        exit;
    }
}