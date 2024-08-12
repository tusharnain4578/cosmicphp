<?php

namespace Core\Utilities;

use ReflectionClass;

class Classic
{
    private static array $reflections = [];
    public static function reflection(string $class): ReflectionClass
    {
        return self::$reflections[$class] ??= (function () use (&$class): ReflectionClass{
            self::validateClass($class);
            return new ReflectionClass($class);
        })();
    }


    private static function validateClass(string $class)
    {
        if (!class_exists($class))
            throw new \Exception("Class $class not exists.");
    }
}