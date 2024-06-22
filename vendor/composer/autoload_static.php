<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9ab0a776549807df98843e3379740be8
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Framework\\' => 10,
        ),
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Framework\\' => 
        array (
            0 => __DIR__ . '/../..' . '/framework',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9ab0a776549807df98843e3379740be8::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9ab0a776549807df98843e3379740be8::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit9ab0a776549807df98843e3379740be8::$classMap;

        }, null, ClassLoader::class);
    }
}
