<?php

namespace Core\Utilities;

// The rootPath() function is base for all baths, make it return path correctly, all other will work accordingly

class Path
{
    private static array $paths = [];

    /**
     * return the path of the root directory of application
     */
    public static function rootPath(string $appendPath = ''): string
    {
        $key = 'root_path' . $appendPath;
        return self::$paths[$key] ?? (self::$paths[$key] ??= Path::join(dirname(FCPATH), $appendPath));
    }
    /**
     * return the path of the app directory
     */
    public static function appPath(string $appendPath = ''): string
    {
        $key = 'app_path' . $appendPath;
        return self::$paths[$key] ?? (self::$paths[$key] ??= Path::join(Path::rootPath('app'), $appendPath));
    }
    /**
     * return the path of the framework directory
     */
    public static function frameworkPath(string $appendPath = '')
    {
        $key = 'framework_path' . $appendPath;
        return self::$paths[$key] ?? (self::$paths[$key] ??= Path::join(Path::rootPath('core'), $appendPath));
    }
    /**
     * return the path of the writable directory
     */
    public static function writablePath(string $appendPath = '')
    {
        $key = 'writable_path' . $appendPath;
        return self::$paths[$key] ?? (self::$paths[$key] ??= Path::join(Path::rootPath('writable'), $appendPath));
    }

    public static function join(...$paths)
    {
        $joinedPath = '';
        $dirSep = DIRECTORY_SEPARATOR;
        foreach ($paths as $path) {
            $path = trim($path);
            if (!empty($path))
                $joinedPath .= $dirSep . $path;
        }
        $joinedPath = ($dirSep === '\\' ? '' : $dirSep) . trim($joinedPath, "\{$dirSep}\ ");
        return $joinedPath;
    }
}