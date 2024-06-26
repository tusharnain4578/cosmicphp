<?php

namespace App\Config;


/**
 * Constants of this file must be static.
 */
class database
{
    // a default connection must be in the list
    private static array $default = [
        'hostname' => 'localhost',
        'username' => 'admin',
        'password' => 'Tushar@4578',
        'database' => 'testDb',
        'port' => 3306,
    ];

    private static array $testDb = [
        'hostname' => 'localhost',
        'username' => 'admin',
        'password' => 'Tushar@4578',
        'database' => 'testDb2',
        'port' => 3306,
    ];
}