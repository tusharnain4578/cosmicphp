<?php
use Core\Database\DBConnection;
use Core\Database\QueryBuilder;
use Core\Request;
use Core\Response;

function dd(...$data)
{
    foreach ($data as $dt) {
        if (request()->isCli())
            var_dump($dt);
        else {
            echo '<pre>';
            echo var_dump($dt);
            echo '</pre>';
        }
    }
    die;
}

function app(): \Core\App
{
    return \Core\App::getInstance();
}

function env(string $name, mixed $default = null): mixed
{
    $value = $_ENV[$name] ?? null;
    if (is_null($value) && !is_null($default))
        return $default;
    return $value ?? null;
}

function request(bool $shared = true): Request
{
    return Request::getInstance(shared: $shared);
}
function response(bool $shared = true): Response
{
    return Response::getInstance(shared: $shared);
}

function get_commandLine_arg(int $index = null)
{
    global $argc, $argv;
    if ($argc < 2)
        return !is_null($index) ? null : [];
    $args = array_slice($argv, 1);
    if (!is_null($index))
        return $args[$index] ?? null;
    return $args;
}


function pdo_instance(string $group = 'default'): \PDO|null
{
    return DBConnection::pdo(name: $group);
}

function db(string $group = 'default', bool $shared = true): QueryBuilder
{
    return QueryBuilder::getInstance(group: $group, shared: $shared);
}

function view(string $view, array $data = []): string
{
    return (new \Core\View)->render($view, $data);
}

function cache(bool $shared = true): \Core\Services\Cache
{
    return \Core\Services\Cache::getInstance(shared: $shared);
}

function base_url(?string $relativeRoute = null): string
{
    return request()->getBaseUrl($relativeRoute);
}

function route(string $name, ...$params): string
{
    return app()->router->route($name, ...$params);
}

function db_escape($input)
{
    // Use a regular expression to allow only alphanumeric characters, underscores, and hyphens
    $sanitizedInput = preg_replace('/[^a-zA-Z0-9_-]/', '', $input);
    return $sanitizedInput;
}