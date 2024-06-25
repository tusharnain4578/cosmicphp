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
}