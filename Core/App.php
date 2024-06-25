<?php

namespace Core;

use Core\Console\CLI;
use Core\Utilities\ClassUtil;
use Core\Utilities\Path;
use Core\Database\DBConnection;

class App
{
    private Request $request;
    public Router $router;
    private CLI $cli;
    private array $appConfig;
    private bool $isAppRunning = false;

    // Options setting names
    public const
        ENABLE_CLI = 'enable_cli';

    private static ?App $instace;

    public static function getInstance(): App
    {
        return self::$instace ??= new App;
    }

    public function __construct()
    {
        // preventing to make more than 1 instance
        if (!isset(self::$instace) || !self::$instace) {
            Autoload::loadHelper(helperName: 'global', helperDirectoryPath: Path::frameworkPath('helpers'));
            $this->request = new Request;
            self::$instace = $this;
        } else {
            throw new \Exception("There can be only 1 instance of app throughout the application.");
        }
    }

    private function setupApplication()
    {
        $this->appConfig = ClassUtil::getClassAllConstants(\App\Config\app::class);

        // First Loading .env file
        Autoload::loadEnv();

        // Setup Autoload Files
        Autoload::loadAppHelpers();

        // From CLI and Router only 1 can be active at a time, they must be setup in end of application
        if ($this->request->isCli()) {
            if (($this->appConfig['ENABLE_CLI'] ?? true) && !isset($this->cli))
                ($this->cli = new CLI)->run();
        } else {
            if (!isset($this->router))
                ($this->router = new Router())->init();
        }
    }

    public function run()
    {
        try {
            if (!$this->isAppRunning) {
                $this->isAppRunning = true;
                $this->setupApplication();
            }

        } catch (\Exception $e) {
            throw $e;
        }
    }


}