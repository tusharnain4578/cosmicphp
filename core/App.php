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

        // Setup Database Connection, if credentials given
        DBConnection::initializeConnection();

        // Setup Router
        if (!$this->request->isCli() && !isset($this->router))
            ($this->router = new Router())->init();

        // Setup CLI
        // cli must be setup in last
        if (($this->appConfig['ENABLE_CLI'] ?? true) && !isset($this->cli) && $this->request->isCli())
            ($this->cli = new CLI)->run();
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