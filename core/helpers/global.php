<?php
use Core\Database\DBConnection;
use Core\Database\QueryBuilder;
use Core\Request;

function dd(...$data)
{
    foreach ($data as &$dt) {
        var_dump($dt);
    }
    die;
}

function app(): \Core\App
{
    return \Core\App::getInstance();
}

function request(bool $shared = true): Request
{
    return Request::getInstance(shared: $shared);
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