<?php

namespace App\Config;


/**
 * Constants of this file must be public.
 */
class database
{
    // a default connection must be in the list
    public const CONNECTION_ARRAY = [
        'default' => [
            'hostname' => 'localhost',
            'username' => 'admin',
            'password' => 'Tushar@4578',
            'database' => 'testDb',
            'port' => 3306,
        ]
    ];

}