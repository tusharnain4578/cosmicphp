<?php

namespace Core\Services;

/**
 * Singleton Method
 */
class Session
{
    private const SESSION_COOKIE_NAME = 'cosmic_session';
    private const SESSION_KEY = '__core';
    private const FLASH_KEY = '__flash';
    private static Session $sharedInstance;
    public function __construct()
    {
        if (isset(self::$sharedInstance))
            throw new \Exception("Only 1 object can be made from Session class. Use Session::getInstance() instead.");
        $this->start();
        $this->markFlashRemove();
        self::$sharedInstance = $this;
    }
    public function __destruct()
    {
        $this->removeFlash();
    }
    public static function getInstance(): self
    {
        return self::$sharedInstance ??= new Session;
    }
    public function start(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_name(self::SESSION_COOKIE_NAME);
            session_start();
        }
    }
    public function close(): void
    {
        session_write_close();
    }
    public function set(string $key, $value): void
    {
        $_SESSION[self::SESSION_KEY][$key] = $value;
    }
    public function get(string $key, $default = null): mixed
    {
        return $_SESSION[self::SESSION_KEY][$key] ?? $default;
    }
    public function pull(string $key, $default = null): mixed
    {
        $data = $_SESSION[self::SESSION_KEY][$key] ?? $default;
        unset($_SESSION[self::SESSION_KEY][$key]);
        return $data;
    }
    public function has(string $key): bool
    {
        return isset($_SESSION[self::SESSION_KEY][$key]);
    }
    public function increment(string $key, int|float $step = 1): void
    {
        $_SESSION[self::SESSION_KEY][$key] = ($_SESSION[self::SESSION_KEY][$key] ?? 0) + $step;
    }

    public function decrement(string $key, int|float $step = 1): void
    {
        $_SESSION[self::SESSION_KEY][$key] = ($_SESSION[self::SESSION_KEY][$key] ?? 0) - $step;
    }
    public function remove(string $key): mixed
    {
        return $this->pull($key, default : null);
    }
    public function setFlash(string|array $key, $value = null): void
    {
        if (is_array($key)) {
            if (!isset($value)) {
                foreach ($key as $k => $v)
                    $_SESSION[self::FLASH_KEY][$k] = ['remove' => false, 'value' => $v];
            } else {
                throw new \InvalidArgumentException("\$value is required as, \$key is a string.");
            }
        }
        $_SESSION[self::FLASH_KEY][$key] = ['remove' => false, 'value' => $value];
    }
    public function getFlash(string $key, $default = null): mixed
    {
        return $_SESSION[self::FLASH_KEY][$key]['value'] ?? $default;
    }
    public function pullFlash(string $key, $default = null): mixed
    {
        $data = $_SESSION[self::FLASH_KEY][$key]['value'] ?? $default;
        unset($_SESSION[self::FLASH_KEY][$key]);
        return $data;
    }
    public function hasFlash(string $key): bool
    {
        return isset($_SESSION[self::FLASH_KEY][$key]);
    }

    public function regenerate(bool $deleteOldSession = true): void
    {
        session_regenerate_id($deleteOldSession);
    }
    private function markFlashRemove(): void
    {
        $sessionData = $_SESSION[self::FLASH_KEY] ?? null;
        if (!is_null($sessionData))
            foreach ($sessionData as $key => $data)
                $_SESSION[self::FLASH_KEY][$key]['remove'] = true;
    }
    private function removeFlash(bool $force = false): void
    {
        $sessionData = $_SESSION[self::FLASH_KEY] ?? null;
        if (!is_null($sessionData)) {
            foreach ($sessionData as $key => $data) {
                if ($force || $data['remove'])
                    unset($_SESSION[self::FLASH_KEY][$key]);
            }
        }
    }






    public static function __callStatic($method, $args)
    {
        return self::getInstance()->$method(...$args);
    }
}