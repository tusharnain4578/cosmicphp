<?php

namespace Core\Config;

use Core\Interfaces\IMiddleware;

class middleware
{
    public static array $aliases = [
        'csrf' => \Core\Middlewares\Csrf::class,
    ];

    public static array $global = [
        'before' => [

        ],
        'after' => [

        ]
    ];


    public static function getMiddlewareFromAlias(string $alias, array $aliases = []): IMiddleware|null
    {
        $class = self::$aliases[$alias] ?? $aliases[$alias] ?? null;
        return $class ? new $class() : null;
    }

}