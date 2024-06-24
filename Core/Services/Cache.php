<?php

namespace Core\Services;

use Core\Console\Console;
use Core\Utilities\Path;
use Core\Console\CLI;
use Core\Utilities\File;
use InvalidArgumentException;

class Cache
{
    private string $cacheDirectory;

    private static Cache $sharedInstance;

    // Array Keys
    private const CACHE_EXPIRE_AFTER = 'expire_after';
    private const CACHE_DATA = 'data';

    public function __construct()
    {
        $this->cacheDirectory = Path::writablePath('cache');
    }

    public static function getInstance(bool $shared = true): Cache
    {
        if ($shared)
            return self::$sharedInstance ?? (self::$sharedInstance ??= new Cache);
        return new Cache;
    }


    /**
     * set cache with file name as $name
     * Give $expireAfter, it will set expire for cache, its as same as we give input in strtotime() like +1 sec, +1 month, etc
     */
    public function set(string $name, mixed $value, string $expireAfter)
    {
        $this->validateCacheName(name: $name);

        if (is_numeric($expireAfter))
            throw new InvalidArgumentException("Not a valid value for $expireAfter, give like +1 day, +5 seconds, etc");

        $cacheFilePath = Path::join($this->cacheDirectory, $name);

        $expireAfter = strtotime($expireAfter);

        if ($expireAfter < time())
            throw new InvalidArgumentException("Not a valid value of $expireAfter, its giving older time.");

        $data = [self::CACHE_EXPIRE_AFTER => $expireAfter, self::CACHE_DATA => $value];

        file_put_contents($cacheFilePath, serialize($data));
    }

    /**
     * get the cache value from cache name
     * If the cache is expired, it will return null
     */
    public function get(string $name): mixed
    {
        $this->validateCacheName(name: $name);

        $cacheFilePath = Path::join($this->cacheDirectory, $name);

        if (!file_exists($cacheFilePath))
            return null;

        $cacheFileContent = file_get_contents($cacheFilePath);
        $data = unserialize($cacheFileContent);

        if (!isset($data[self::CACHE_EXPIRE_AFTER]) or !isset($data[self::CACHE_DATA]))
            return null;

        // checking if expired
        if (time() > $data[self::CACHE_EXPIRE_AFTER]) {
            unlink($cacheFilePath);
            return null;
        }

        return $data[self::CACHE_DATA];
    }

    /**
     * It will delete the cache
     */
    public function delete(string $name)
    {
        $this->validateCacheName(name: $name);
        $cacheFilePath = Path::join($this->cacheDirectory, $name);
        if (file_exists($cacheFilePath))
            unlink($cacheFilePath);
    }

    /**
     * Delete all cache
     */
    public function deleteAll()
    {
        $files = File::scan_directory($this->cacheDirectory, returnFullPath: true);
        foreach ($files as &$file)
            if (!str_ends_with($file, 'index.html')) // excluding index.html file
                File::delete($file);
        if (request()->isCli())
            Console::success("Cache cleared!");
    }


    /**
     * PHP File Cache, create file with the value as content
     * NOTE * -> It can't have expiry time, its for forever, until you refresh the cache
     */
    public function setPHPFileCache(string $filename, string $content)
    {
        $this->validateFileCacheName(filename: $filename);
        $cacheFilePath = Path::join($this->cacheDirectory, $filename);
        file_put_contents($cacheFilePath, $content);
    }
    /**
     * File Cache, gets the content of cached file
     * NOTE * -> It doesnt have any expiration, so if it returns null, it means cache doesnt exists
     */
    public function getPHPFileCache(string $filename)
    {
        $this->validateFileCacheName(filename: $filename);
        $cacheFilePath = Path::join($this->cacheDirectory, $filename);
        if (file_exists($cacheFilePath)) {
            $data = require_once ($cacheFilePath);
            return $data;
        }
        return null;
    }


    public static function handleCommand(array $args)
    {
        $param = $args[0];

        if ($param === 'cache:clear') {
            cache()->deleteAll();
        } else {
            CLI::invalidParamMessage();
        }
    }






    // Private methods
    private function validateCacheName(string $name)
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $name)) {
            throw new InvalidArgumentException("Invalid filename format. Only alphanumeric characters and underscore (_) are allowed.");
        }
    }
    private function validateFileCacheName(string $filename)
    {
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_.-]*\.php$/', $filename)) {
            throw new InvalidArgumentException("Invalid filename format. Must start with a letter, should not have any spaces, and can only contain letters, numbers, underscores (_), hyphens (-), and periods (.), and must end with '.php'");
        }
    }
}