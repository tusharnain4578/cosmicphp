<?php

namespace Core\Database;

use InvalidArgumentException;
use PDO;
use PDOStatement;
use stdClass;

class QueryBuilder
{
    private PDO $pdo;
    private string $table;
    private string|array $columns;
    private array $wheres;
    private array $selectPreparedData;
    private ?PDOStatement $selectStatement;



    private static array $sharedInstances = [];

    const VALID_WHERE_OPERATORS = ['=', '<', '>', '<=', '>=', '!=', 'LIKE', 'NOT LIKE', 'BETWEEN', 'NOT BETWEEN', 'IN', 'NOT IN', 'IS NULL', 'IS NOT NULL', 'REGEXP'];

    public function __construct(string $group = 'default')
    {
        $pdo = pdo_instance(group: $group);
        if (!$pdo)
            throw new \Exception("Get null instead of PDO Object.");
        $this->pdo = $pdo;
        $this->resetBuilder();
    }

    public static function getInstance(string $group = 'default', bool $shared = true): QueryBuilder
    {
        if ($shared) {
            return self::$sharedInstances[$group] ?? (self::$sharedInstances[$group] ??= new QueryBuilder(group: $group));
        }
        return new QueryBuilder(group: $group);
    }

    public function resetBuilder()
    {
        $this->table = '';
        $this->columns = '*';
        $this->wheres = [];
        $this->selectPreparedData = [];
        $this->selectStatement = null;
    }


    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }
    public function select(string|array $columns = '*'): self
    {
        $this->columns = $columns;
        return $this;
    }
    public function where(string $column, string $operator, null|string|int|float|array $value = null): self
    {
        $operator = trim(strtoupper($operator));
        if (!in_array($operator, self::VALID_WHERE_OPERATORS))
            throw new InvalidArgumentException("Invalid operator provided: $operator");

        $this->wheres[] = [
            'type' => 'AND',
            'column' => $column,
            'operator' => $operator,
            'value' => $this->getWhereValueString(operator: $operator, value: $value)
        ];
        return $this;
    }
    public function orWhere(string $column, string $operator, string|int|float|array $value): self
    {
        if (!in_array($operator, self::VALID_WHERE_OPERATORS))
            throw new InvalidArgumentException("Invalid operator provided: $operator");

        $this->wheres[] = [
            'type' => 'OR',
            'column' => $column,
            'operator' => $operator,
            'value' => $this->getWhereValueString(operator: $operator, value: $value)
        ];
        return $this;
    }
    public function get(): self
    {
        $fields = $this->_backtick($this->columns);
        $fields = $fields === "`*`" ? '*' : $fields;

        $table = $this->_backtick($this->table);

        $sql = 'SELECT ' . $fields
            . ' FROM ' . $table;

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ';
            foreach ($this->wheres as $index => $where) {
                $whereColumn = $this->_backtick($where['column']);
                if ($index > 0)
                    $sql .= $where['type'] . ' ';
                $sql .= $whereColumn . ' ' . $where['operator'] . $where['value'];
            }
        }

        $this->selectStatement = $this->pdo->prepare($sql);
        $this->selectStatement->execute($this->selectPreparedData);

        return $this;
    }

    /**
     * @return stdClass|null
     */
    public function row(): stdClass|null
    {
        if ($this->selectStatement)
            return $this->selectStatement->fetch();
        $this->resetBuilder();
        return null;
    }

    /**
     * @return stdClass[]
     */
    public function result(): array
    {
        if ($this->selectStatement)
            return $this->selectStatement->fetchAll();
        $this->resetBuilder();
        return [];
    }





    // Insert Query

    /**
     * insert the array $data in selected table
     * 
     * if $returnId is true, then it will return the id of inserted record
     * else it will return the 0/1 status of insertion
     */
    public function insert(array $data, $returnId = false): int
    {
        if (empty($data))
            throw new \Exception("Empty Dataset, Nothing to insert.");

        $table = $this->_backtick($this->table);
        $columns = $this->_backtick(array_keys($data));
        $valuesArray = array_values($data);
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

        $statement = $this->pdo->prepare($sql);

        $insertStatus = $statement->execute($valuesArray);

        $this->resetBuilder();

        if ($returnId)
            return $this->pdo->lastInsertId();

        return $insertStatus;
    }


    /**
     * Updates a record by its id in the selected table
     */
    public function updateById(int $id, array $data, string $idFieldName = 'id')
    {
        if (empty($data))
            throw new \Exception("Empty Dataset, Nothing to update.");


        $table = $this->_backtick($this->table);
        $columnsArray = array_keys($data);
        $valuesArray = array_values($data);
        $idFieldName = $this->_backtick($idFieldName);

        $setString = '';
        foreach ($columnsArray as $column)
            $setString .= "`$column` = ?, ";
        $setString = rtrim($setString, '\ \,');

        $sql = "UPDATE $table SET $setString WHERE $idFieldName = $id";

        $statement = $this->pdo->prepare($sql);

        $this->resetBuilder();

        return $statement->execute($valuesArray);
    }




    public function query(string $sql): bool|int
    {
        return $this->pdo->exec(statement: $sql);
    }

    public function tableExists(string $tableName)
    {
        $tableName = db_escape($tableName);
        return $this->pdo->query("SHOW TABLES LIKE '$tableName';")->fetch() === false ? false : true;
    }

    // Private Methods



    /**
     * Helper Method for where clause
     */
    private function getWhereValueString(string $operator, null|string|int|float|array $value): string
    {

        $resultString = '';

        switch ($operator) {

            case 'IS NULL':
            case 'IS NOT NULL': {
                $resultString = '';
                $value = null;
                break;
            }

            case 'BETWEEN':
            case 'NOT BETWEEN ': {
                if (is_null($value) || !is_array($value) || (count($value) != 2))
                    throw new InvalidArgumentException("$operator Operator requires array of count 2.");
                $resultString = ' ? AND ? ';
                break;
            }

            case 'IN':
            case 'NOT IN': {
                if (is_null($value) || !is_array($value) or empty($value))
                    throw new InvalidArgumentException("$operator Operator requires a non-empty array.");
                $resultString = ' (' . trim(str_repeat(' ? , ', count($value)), '\,\ ') . ') ';
                break;
            }

            default: {
                if (is_null($value) || is_array($value))
                    throw new InvalidArgumentException("$operator Operator requires int,float or string value.");
                $resultString = ' ? ';
                break;
            }
        }

        if (!is_null($value)) {
            if (is_array($value)) {
                foreach ($value as $val)
                    $this->selectPreparedData[] = $val;
            } else {
                $this->selectPreparedData[] = $value;
            }
        }

        return $resultString;
    }


    /**
     * Helper method to wrap string in backticks for sql friendly strings
     */
    private function _backtick(string|array $data): string
    {
        if (is_string($data))
            $data = explode(',', $data);
        $data = array_map('trim', $data);
        return '`' . implode('`,`', $data) . '`';
    }
}