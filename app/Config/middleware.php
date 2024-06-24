<?php

namespace App\Config;

use Core\Interfaces\IMiddleware;

class middleware
{
    public static array $aliases = [
        'auth' => \App\Middlewares\Auth::class
    ];

    public static function getMiddlewareFromAlias(string $alias): IMiddleware|null
    {
        $class = self::$aliases[$alias] ?? null;
        return $class ? new $class() : null;
    }
}