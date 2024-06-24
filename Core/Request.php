<?php

namespace Core;

use App\Config\request as requestConfig;

class Request
{
    private ?string $uri = null;
    private ?string $baseUrl = null;
    private static Request $sharedRequest;
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';

    public static function getInstance(bool $shared = false): Request
    {
        if ($shared)
            return self::$sharedRequest ??= new Request;
        return new Request;
    }


    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? ($this->isCli() ? 'CLI' : '');
    }


    public function startingScriptUrl(): string
    {
        return $_SERVER['SCRIPT_NAME'];
    }


    public function isGet(): bool
    {
        return $this->method() === self::METHOD_GET;
    }


    public function isPost(): bool
    {
        return $this->method() === self::METHOD_POST;
    }


    public function isCli(): bool
    {
        return strtoupper(php_sapi_name()) == 'CLI';
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

    public function getBaseUrl(?string $relativeRoute = null): string
    {
        $baseUrl = $this->baseUrl ?? trim(($this->baseUrl ??= env('BASE_URL', '')), '\/\ ');
        $relativeRoute = $relativeRoute ? '/' . trim($relativeRoute, '\/\ ') : '';
        return $baseUrl . $relativeRoute;
    }

    public function input(?string $key = null)
    {
        if (isset($key)) {
            $value = $_POST[$key] ?? $_GET[$key] ?? null;
            return requestConfig::input_gate($value);
        }

        $inputs = array_merge($_GET, $_POST);
        foreach ($inputs as $key => $value)
            $inputs[$key] = requestConfig::input_gate($value);
        return $inputs;
    }


    public function inputGet(?string $key = null)
    {
        if (isset($key)) {
            $value = $_GET[$key] ?? null;
            return requestConfig::input_gate($value);
        }
        foreach ($_GET as $key => $value)
            $inputs[$key] = requestConfig::input_gate($value);
        return $inputs;
    }

    public function inputPost(?string $key = null)
    {
        if (isset($key)) {
            $value = $_POST[$key] ?? null;
            return requestConfig::input_gate($value);
        }
        foreach ($_POST as $key => $value)
            $inputs[$key] = requestConfig::input_gate($value);
        return $inputs;
    }
}