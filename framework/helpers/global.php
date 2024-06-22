<?php
use Framework\Database\DBConnection;
use Framework\Database\QueryBuilder;
use Framework\Request;

function dd(...$data)
{
    foreach ($data as &$dt) {
        var_dump($dt);
    }
    die;
}

function app(): \Framework\App
{
    return \Framework\App::getInstance();
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
    return (new \Framework\View)->render($view, $data);
}

function cache(bool $shared = true): \Framework\Services\Cache
{
    return \Framework\Services\Cache::getInstance(shared: $shared);
}