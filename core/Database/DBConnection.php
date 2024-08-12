<?php

namespace Core\Database;

use Core\Utilities\Classic;
use App\Config\database as databaseConfig;
use \PDO;

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

            $host = env("db.$group.hostname") ?? $connection['hostname'];
            $username = env("db.$group.username") ?? $connection['username'];
            $database = env("db.$group.database") ?? $connection['database'];
            $password = env("db.$group.password") ?? $connection['password'];
            $port = env("db.$group.port") ?? $connection['port'] ?? 3306;
            $charset = 'utf8mb4';
            $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=$charset";

            return new PDO($dsn, $username, $password, self::PDO_OPTIONS);

        }

        throw new \Exception("Invalid Database Group : '$group'.");
    }


    public static function pdo(string $name): PDO|null
    {
        return self::$conns[$name] ?? (self::$conns[$name] ??= self::getConnection($name));
    }





}