<?php

namespace Core;

use Core\Console\CLI;
use App\Config\UtilityConfig;

class Request
{
    private ?string $uri = null;
    private static ?string $baseUrl = null;
    private static Request $sharedRequest;
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHODS = ['GET', 'POST'];
    public const PHP_SAPI_CLI = 'cli';
    public const PHP_SAPI_CLI_SERVER = 'cli-server';
    public const DEFAULT_BASE_URL = 'http://localhost';

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
        return strtolower(php_sapi_name()) == Request::PHP_SAPI_CLI;
    }
    public function isCliServer(): bool
    {
        return strtolower(php_sapi_name()) == Request::PHP_SAPI_CLI_SERVER;
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
        $baseUrl = self::$baseUrl ?? (function (): string{
            $envBaseUrl = $this->isCliServer() ? env('DEVELOPMENT_SERVER_BASE_URL', CLI::DEFAULT_DEV_SERVER_BASE_URL) : env('BASE_URL', Request::DEFAULT_BASE_URL);
            return trim((self::$baseUrl ??= $envBaseUrl), '\/\ ');
        })();

        $relativeRoute = $relativeRoute ? '/' . trim($relativeRoute, '\/\ ') : '';
        return $baseUrl . $relativeRoute;
    }

    public function input(?string $key = null)
    {
        if (isset($key)) {
            $value = $_POST[$key] ?? $_GET[$key] ?? null;
            return UtilityConfig::request_input_gate($value);
        }

        $inputs = array_merge($_GET, $_POST);
        foreach ($inputs as $key => $value)
            $inputs[$key] = UtilityConfig::request_input_gate($value);
        return $inputs;
    }


    public function inputGet(?string $key = null)
    {
        if (isset($key)) {
            $value = $_GET[$key] ?? null;
            return UtilityConfig::request_input_gate($value);
        }
        foreach ($_GET as $key => $value)
            $inputs[$key] = UtilityConfig::request_input_gate($value);
        return $inputs;
    }

    public function inputPost(?string $key = null)
    {
        if (isset($key)) {
            $value = $_POST[$key] ?? null;
            return UtilityConfig::request_input_gate($value);
        }
        foreach ($_POST as $key => $value)
            $inputs[$key] = UtilityConfig::request_input_gate($value);
        return $inputs;
    }
}