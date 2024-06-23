<?php

namespace Core\Utilities;

class ClassUtil
{
    public static function getClassAllConstants(string $class): array
    {
        self::validateClass($class);
        return (new \ReflectionClass($class))->getConstants();
    }


    private static function validateClass(string $class)
    {
        if (!class_exists($class))
            throw new \Exception("Class $class not exists.");
    }
}