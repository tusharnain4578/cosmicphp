<?php

namespace App\Config;


/**
 * Constants of this file must be static.
 */
class database
{
    // a default connection must be in the list
    private static array $default = [
        'hostname' => '',
        'username' => '',
        'password' => '',
        'database' => '',
        'port' => 3306,
    ];
}