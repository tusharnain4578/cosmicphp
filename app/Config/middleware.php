<?php

namespace App\Config;

use Core\Interfaces\IMiddleware;

class middleware
{
    public static array $aliases = [
        'test' => \App\Middlewares\TestMiddleware::class
    ];

    public static array $global = [
        'before' => [
            // 'csrf'
        ],
        'after' => [

        ]
    ];


}