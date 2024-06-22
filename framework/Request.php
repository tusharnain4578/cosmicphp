<?php

namespace Framework;

class Request
{
    private ?string $uri = null;

    private static Request $sharedRequest;
    public static function getInstance(bool $shared = false): Request
    {
        if ($shared)
            return self::$sharedRequest ??= new Request;
        return new Request;
    }

    public function method(): string|null
    {
        return $_SERVER['REQUEST_METHOD'] ?? ($this->isCli() ? 'CLI' : null);
    }
    public function startingScriptUrl(): string
    {
        return $_SERVER['SCRIPT_NAME'];
    }
    public function isCli(): bool
    {
        return php_sapi_name() === 'cli';
    }
    public function getUri(): string
    {
        if (!is_null($this->uri))
            return $this->uri;

        $requestUri = parse_url(urldecode($_SERVER['REQUEST_URI']));
        $requestPath = $requestUri['path'];
        $scriptDir = substr($this->startingScriptUrl(), 0, -strlen('/server.php'));
        $requestPath = substr($requestPath, strlen($scriptDir));

        $requestPath = trim($requestPath, '\/\ ');
        return $this->uri = $requestPath;
    }
}