<?php

namespace Framework;

use Framework\Console\CLI;
use Framework\Database\DBConnection;

class App
{
    private Request $request;
    private Router $router;
    private CLI $cli;


    // Options setting names
    public const
        LOAD_ENV = 'load_env',
        AUTOLOAD_HELPERS = 'autoload_helpers',
        DATABASE_CONNECTIONS = 'database_connections',
        ENABLE_ROUTING = 'enable_routing',
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
            Autoload::loadHelper(helper: 'global', helperDirectoryPath: Path::frameworkPath('helpers'));
            $this->request = new Request;
            self::$instace = $this;
        } else {
            throw new \Exception("There can be only 1 instance of app throughout the application.");
        }
    }


    private function setupAutoloadHelpers(array $helpersList)
    {
        foreach ($helpersList as $helper)
            Autoload::loadHelper($helper);
    }


    private function setupDB(array $connectionArray)
    {
        if (!empty($connectionArray)) {
            DBConnection::initializeConnection($connectionArray);
        }
    }


    private function setupRouter()
    {
        if (!$this->request->isCli() && !isset($this->router)) {
            $this->router = new Router();
            $this->router->init();
        }
    }


    private function setupCLI()
    {
        if (!isset($this->cli) && $this->request->isCli()) {
            $this->cli = new CLI;
            $this->cli->run();
        }
    }


    public function run(array $options = [])
    {
        if ($options[App::LOAD_ENV] ?? true)
            Autoload::loadEnv();

        if ($options[App::AUTOLOAD_HELPERS] ?? [])
            $this->setupAutoloadHelpers(helpersList: $options[App::AUTOLOAD_HELPERS]);

        if ($options[App::DATABASE_CONNECTIONS] ?? [])
            $this->setupDB(connectionArray: $options[App::DATABASE_CONNECTIONS]);

        if ($options[App::ENABLE_ROUTING] ?? false)
            $this->setupRouter();

        if ($options[App::ENABLE_CLI] ?? false)
            $this->setupCLI();

    }


}