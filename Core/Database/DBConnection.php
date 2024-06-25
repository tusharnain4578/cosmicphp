<?php

namespace Core\Database;

use Core\Console\Console;
use \PDO;
use PDOException;

abstract class DBConnection
{
    /**
     * @var list<array>
     */
    private const CONNECTION_ARRAY = \App\Config\database::CONNECTION_ARRAY;
    private const PDO_OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,     // Fetch results as objects
        PDO::ATTR_PERSISTENT => true,                       // Enable persistent connections
        PDO::ATTR_EMULATE_PREPARES => false,                // Disable emulated prepared statements
    ];


    /**
     * @var PDO[]
     */
    private static array $conns;


    private static function getConnection(string $group = 'default'): PDO
    {
        $connection = self::CONNECTION_ARRAY[$group];

        $host = $connection['hostname'];
        $username = $connection['username'];
        $database = $connection['database'];
        $password = $connection['password'];

        $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";

        return new PDO($dsn, $username, $password, self::PDO_OPTIONS);
    }


    public static function pdo(string $name): PDO|null
    {
        return self::$conns[$name] ?? (self::$conns[$name] ??= self::getConnection($name));
    }

    // public static function transaction(callable $func, ?PDO $pdo = null)
    // {
    //     $pdo = is_null($pdo) ? self::pdo('default') : $pdo;

    //     $pdo->beginTransaction();

    //     try {

    //         $func($pdo); // use only this pdo inside the transaction call back

    //         if (!$pdo->inTransaction())
    //             throw new \Exception("Transaction ended prematurely");

    //         $pdo->commit();

    //     } catch (\Exception $e) {

    //         if ($pdo->inTransaction())
    //             $pdo->rollBack();

    //         throw $e;
    //     }
    // }



}