<?php

namespace App\Config;

use Core\App as FmApp;

class App
{
    public const OPTIONS = [
        FmApp::AUTOLOAD_HELPERS => \App\Config\Autoload::AUTOLOAD_HELPERS,
        FmApp::DATABASE_CONNECTIONS => \App\Config\Database::CONNECTION_ARRAY,
        FmApp::ENABLE_ROUTING => true,
        FmApp::ENABLE_CLI => true
    ];

}