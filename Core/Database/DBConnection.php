<?php

namespace Core\Database;

use Core\Console\Console;
use Core\Utilities\Classic;
use App\Config\database as databaseConfig;
use \PDO;
use PDOException;

abstract class DBConnection
{
    /**
     * @var list<array>
     */
    private const PDO_OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // Fetch results as associative array
        PDO::ATTR_PERSISTENT => true,                       // Enable persistent connections
        PDO::ATTR_EMULATE_PREPARES => false,                // Disable emulated prepared statements
    ];

    private static ?array $connectionArray = null;


    /**
     * @var PDO[]
     */
    private static array $conns;


    private static function getConnection(string $group = 'default'): PDO
    {
        if (is_null(self::$connectionArray)) {
            self::$connectionArray = Classic::reflection(databaseConfig::class)->getStaticProperties() ?? [];
        }


        if (isset(self::$connectionArray[$group]) && is_array(self::$connectionArray[$group])) {

            $connection = self::$connectionArray[$group];

            $host = $connection['hostname'];
            $username = $connection['username'];
            $database = $connection['database'];
            $password = $connection['password'];
            $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";

            return new PDO($dsn, $username, $password, self::PDO_OPTIONS);

        }

        throw new \Exception("Invalid Database Group : '$group'.");
    }


    public static function pdo(string $name): PDO|null
    {
        return self::$conns[$name] ?? (self::$conns[$name] ??= self::getConnection($name));
    }





}