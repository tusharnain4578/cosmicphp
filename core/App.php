<?php

namespace Core;

use Core\Console\CLI;
use Core\Services\Session;
use Core\Utilities\Classic;
use Core\Utilities\Path;
use App\Config\app as appConfig;
use Core\Utilities\Rex;

class App
{
    private Request $request;
    public Router $router;
    public Session $session;
    private CLI $cli;
    private array $appConfig;
    private bool $isAppRunning = false;


    private static ?App $instace;

    public static function getInstance(): App
    {
        return self::$instace ??= new App;
    }

    public function __construct()
    {
        // preventing to make more than 1 instance
        if (!isset(self::$instace) || !self::$instace) {
            self::$instace = $this;
        } else {
            throw new \Exception("There can be only 1 instance of app throughout the application.");
        }
    }

    private function setupApplication()
    {
        Autoload::loadHelper(helperName: 'global', helperDirectoryPath: Path::frameworkPath('helpers'));

        // First Loading .env file
        Autoload::loadEnv();

        // Initializing session
        $this->session = new Session();

        // initializing request object
        $this->request = new Request;

        Rex::init(); // Initializing Date Config

        $this->appConfig = Classic::reflection(appConfig::class)->getConstants();

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

        } catch (\Exception | \Error $e) {
            $exceptionHandler = new ExceptionHandler();
            $exceptionHandler->handle($e);
        }
    }



}