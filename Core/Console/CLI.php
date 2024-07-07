<?php

namespace Core\Console;

use Core\Database\Migration;
use Core\Services\Cache;

class CLI
{
    private array $args = [];
    private ?string $param;
    public const DEFAULT_DEV_SERVER_BASE_URL = 'http://localhost:8080';
    public function __construct($args = [])
    {
        $this->args = get_commandLine_arg();
        $this->param = $this->args[0] ?? '';


        Console::init();
    }
    public function __destruct()
    {
        Console::close();
    }
    public function run()
    {
        Console::success(
            sprintf(
                'Buzz PHP - Command Line Tool - Server Time: %s UTC%s',
                date('Y-m-d H:i:s'),
                date('P')
            )
        );

        $this->handleArguements();
    }

    private function handleArguements()
    {
        if (!$this->param)
            return;

        if (str_starts_with($this->param, 'serve')) {

            self::runDevServer(); // script ends here

        } else if (str_starts_with($this->param, 'migrate')) {

            Migration::handleCommand(args: $this->args);

        } else if (str_starts_with($this->param, 'cache')) {

            Cache::handleCommand(args: $this->args);

        } else if (str_starts_with($this->param, 'create')) {

            FileGenerator::handleConsole(args: $this->args);

        } else {

            self::invalidParamMessage();

        }
    }

    public static function invalidParamMessage($message = 'Invalid Parameter!')
    {
        Console::error(message: $message);
    }

    private static function runDevServer()
    {
        $devServerBaseUrl = env('DEVELOPMENT_SERVER_BASE_URL', CLI::DEFAULT_DEV_SERVER_BASE_URL);
        $devServerBaseUrl = ltrim($devServerBaseUrl, '\http://\https://');
        exec("php -S $devServerBaseUrl -t " . FCPATH);
    }


}