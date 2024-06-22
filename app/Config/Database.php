<?php

namespace App\Config;


class Database
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