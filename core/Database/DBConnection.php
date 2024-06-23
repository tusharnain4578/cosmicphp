<?php

namespace Core\Database;

use Core\Console\Console;
use \PDO;
use PDOException;

abstract class DBConnection
{
    private static array $connectionArray;
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


    public static function initializeConnection(array $connectionArray)
    {
        self::$connectionArray = $connectionArray;

        try {

            foreach (self::$connectionArray as $name => $connection) {
                $host = $connection['hostname'];
                $username = $connection['username'];
                $database = $connection['database'];
                $password = $connection['password'];

                $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";

                self::$conns[$name] = new PDO($dsn, $username, $password, self::PDO_OPTIONS);
            }

        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }


    public static function pdo(string $name): PDO|null
    {
        return self::$conns[$name] ?? null;
    }

    public static function transaction(callable $func, ?PDO $pdo = null)
    {
        $pdo = is_null($pdo) ? self::pdo('default') : $pdo;

        $pdo->beginTransaction();

        try {

            $func($pdo); // use only this pdo inside the transaction call back

            if (!$pdo->inTransaction())
                throw new \Exception("Transaction ended prematurely");

            $pdo->commit();

        } catch (\Exception $e) {

            if ($pdo->inTransaction())
                $pdo->rollBack();

            throw $e;
        }
    }


    public static function insert(string $table, array $data, ?PDO $pdo = null): bool
    {
        try {
            $pdo = is_null($pdo) ? self::pdo('default') : $pdo;

            // Build the query
            $backTickedColumns = array_map(fn($el) => "`$el`", array_keys($data));
            $columns = implode(', ', $backTickedColumns);

            $placeholders = ':' . implode(', :', array_keys($data));
            $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

            // Prepare the statement
            $stmt = $pdo->prepare($sql);
            // Bind parameters
            foreach ($data as $key => $value)
                $stmt->bindValue(":$key", $value);

            // Execute the statement
            return $stmt->execute();

        } catch (\Exception $e) {

            Console::error($e->getMessage());

            throw $e;
        }

    }
}