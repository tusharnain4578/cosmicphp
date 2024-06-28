<?php

namespace Core\Database;

use InvalidArgumentException;
use PDO;
use PDOStatement;
use stdClass;

class QueryBuilder
{
    private PDO $pdo;
    private string $table = '';
    private string|array $columns = '*';
    private array $wheres = [];
    private array $preparedData = [];
    private array $orderBy = [];
    private ?PDOStatement $selectStatement;



    private static array $sharedInstances = [];


    public function __construct(string $group = 'default')
    {
        $pdo = pdo_instance(group: $group);
        if (!$pdo)
            throw new \Exception("Get null instead of PDO Object.");
        $this->pdo = $pdo;
    }

    public static function getInstance(string $group = 'default', bool $shared = true): QueryBuilder
    {
        if ($shared)
            return self::$sharedInstances[$group] ?? (self::$sharedInstances[$group] ??= new QueryBuilder(group: $group));
        return new QueryBuilder(group: $group);
    }

    public function resetBuilder()
    {
        $this->table = '';
        $this->columns = '*';
        $this->wheres = [];
        $this->preparedData = [];
        $this->orderBy = [];
        $this->selectStatement = null;
    }


    public function table(string $table): self
    {
        $this->resetBuilder();
        $this->table = $table;
        return $this;
    }


    public function select(string|array $columns = '*'): self
    {
        $this->columns = $columns;
        return $this;
    }
    public function where($column, $value): self
    {
        $this->handleWhere(column: $column, operator: Operators::EQUALS, value: $value);
        return $this;
    }
    public function orWhere(string $column, $value): self
    {
        $this->handleWhere(column: $column, operator: Operators::EQUALS, value: $value, type: Operators::OR );
        return $this;
    }
    public function whereIn(string $column, array $valueList): self
    {
        $this->handleWhere(column: $column, operator: Operators::IN, value: $valueList);
        return $this;
    }
    public function orWhereIn(string $column, array $valueList): self
    {
        $this->handleWhere(column: $column, operator: Operators::IN, value: $valueList, type: Operators::OR );
        return $this;
    }
    public function whereNotIn(string $column, array $valueList): self
    {
        $this->handleWhere(column: $column, operator: Operators::NOT_IN, value: $valueList);
        return $this;
    }
    public function orWhereNotIn(string $column, array $valueList): self
    {
        $this->handleWhere(column: $column, operator: Operators::NOT_IN, value: $valueList, type: Operators::OR );
        return $this;
    }
    public function whereNot(string $column, string $value): self
    {
        $this->handleWhere(column: $column, operator: Operators::NOT_EQUALS, value: $value);
        return $this;
    }
    public function orWhereNot(string $column, string $value): self
    {
        $this->handleWhere(column: $column, operator: Operators::NOT_EQUALS, value: $value, type: Operators::OR );
        return $this;
    }
    public function whereBetween(string $column, string|int|float $start, string|int|float $end): self
    {
        $this->handleWhere(column: $column, operator: Operators::BETWEEN, value: [$start, $end]);
        return $this;
    }
    public function orWhereBetween(string $column, string|int|float $start, string|int|float $end): self
    {
        $this->handleWhere(column: $column, operator: Operators::BETWEEN, value: [$start, $end], type: Operators::OR );
        return $this;
    }
    public function whereNotBetween(string $column, string|int|float $start, string|int|float $end): self
    {
        $this->handleWhere(column: $column, operator: Operators::NOT_BETWEEN, value: [$start, $end]);
        return $this;
    }
    public function orWhereNotBetween(string $column, string|int|float $start, string|int|float $end): self
    {
        $this->handleWhere(column: $column, operator: Operators::NOT_BETWEEN, value: [$start, $end], type: Operators::OR );
        return $this;
    }
    public function whereNull(string $column): self
    {
        $this->handleWhere(column: $column, operator: Operators::IS_NULL);
        return $this;
    }
    public function whereNotNull(string $column): self
    {
        $this->handleWhere(column: $column, operator: Operators::IS_NOT_NULL);
        return $this;
    }
    public function orderBy(string $column, string $direction): self
    {
        $direction = trim(strtoupper($direction));
        if (!in_array($direction, [Operators::ORDER_DIRECTION_ASC, Operators::ORDER_DIRECTION_DESC]))
            throw new InvalidArgumentException("Order BY Direction : '$direction' is not a valid direction");
        $this->orderBy[] = ['column' => $column, 'direction' => $direction];
        return $this;
    }

    public function get(): self
    {
        $this->__tableRequired();

        $fields = $this->_backtick($this->columns);
        $fields = $fields === "`*`" ? '*' : $fields;

        $sql = "SELECT $fields FROM `$this->table`";
        $sql .= $this->_getWhereString();
        $sql .= $this->_getOrderByString();

        $this->selectStatement = $this->pdo->prepare($sql);
        $this->selectStatement->execute($this->preparedData);

        return $this;
    }


    /**
     * @return stdClass|null
     */
    public function row(): stdClass|null
    {
        if ($this->selectStatement)
            return $this->selectStatement->fetch();
        return null;
    }



    /**
     * @return stdClass[]
     */
    public function result(): array
    {
        if ($this->selectStatement)
            return $this->selectStatement->fetchAll();
        return [];
    }


    /**
     * Returns all the records from the table
     */
    public function all($columns = '*'): array
    {
        $this->__tableRequired();
        return $this->get()->result();
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
        $this->__tableRequired();
        if (empty($data))
            throw new \Exception("Empty Dataset, Nothing to insert.");

        $columns = $this->_backtick(array_keys($data));
        $valuesArray = array_values($data);
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $insertStatus = $this->pdo->prepare("INSERT INTO `$this->table` ($columns) VALUES ($placeholders)")->execute($valuesArray);
        if ($returnId)
            return $this->pdo->lastInsertId();
        return $insertStatus;
    }


    /**
     * Updates a record
     * Usage Example : ->where()->update($data);
     */
    public function update(array $data): bool
    {
        $this->__tableRequired();
        if (empty($data))
            throw new \Exception("Empty Dataset, Nothing to update.");
        $this->_setPreparedData(array_values($data), prepend: true);
        $setString = $this->_getUpdateSetString(data: $data);
        $whereString = $this->_getWhereString();
        return $this->pdo->prepare("UPDATE `$this->table` SET $setString $whereString")->execute($this->preparedData);
    }
    /**
     * Updates a record by id primary key
     */
    public function updateById(int $id, array $data, string $idFieldName = 'id'): bool
    {
        $this->preparedData = [];
        return $this->where($idFieldName, $id)->update($data);
    }

    /**
     * Deletes a record
     * Usage Example : ->where()->delete();
     */
    public function delete(): bool
    {
        $this->__tableRequired();
        $whereString = $this->_getWhereString();
        return $this->pdo->prepare("DELETE FROM `$this->table` $whereString")->execute($this->preparedData);
    }
    /**
     * Deletes a record by its id, primary key
     */
    public function deleteById(int $id, string $idFieldName = 'id'): bool
    {
        $this->preparedData = [];
        return $this->where($idFieldName, $id)->delete();
    }


    public function execute(string $sql): bool|int
    {
        return $this->pdo->exec(statement: $sql);
    }

    public function tableExists(string $tableName)
    {
        $tableName = db_escape($tableName);
        return $this->pdo->query("SHOW TABLES LIKE '$tableName';")->fetch() === false ? false : true;
    }




    // *****************************************************************
    // PRIVATE METHODS
    // *****************************************************************

    private function handleWhere(string $column, string $operator, $value = null, string $type = Operators::AND )
    {
        $operator = $this->_getWhereOperator($operator, $value);
        $this->wheres[] = [
            'type' => $type,
            'column' => $column,
            'operator' => $operator,
            'value' => Operators::getWherePlaceholder(operator: $operator, value: $value)
        ];
        if (!is_null($value))
            $this->_setPreparedData($value);
    }



    private function _getWhereOperator($operator, $value): string
    {
        $op = $operator;
        if (is_null($value)) {
            if ($operator === Operators::EQUALS)
                $op = Operators::IS_NULL;
            else if ($operator === Operators::NOT_EQUALS)
                $op = Operators::IS_NOT_NULL;
        }

        return trim($op);
    }


    /**
     * Helper method to parse and set prepared data
     */
    private function _setPreparedData($value, bool $prepend = false)
    {
        if (!is_null($value)) {
            if (!is_array($value))
                $value = [$value];
            foreach ($value as $val) {
                if (is_bool($val))
                    $val = ($val === true) ? 1 : 0;
                else if (is_null($val))
                    continue; // skipping null, because it will be set in placeholders
                if ($prepend)
                    array_unshift($this->preparedData, $val); // prepend to the beginning of the array
                else
                    $this->preparedData[] = $val; // append to the end of the array
            }
        }
    }


    /**
     * Helper method to generate SET string for Updatethere 
     * $data -> array [columng => value]
     */
    private function _getUpdateSetString(array $data): string
    {
        $setString = '';
        foreach ($data as $column => $value) {
            $column = trim($column);
            $setString .= "`$column` = " .
                (is_null($value) ? 'NULL' : '?') .
                ', ';
        }
        return rtrim($setString, '\ \,');
    }

    /**
     * Helper method generate where string from $this->wheres array
     */
    private function _getWhereString(): string
    {
        $sql = '';
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ';
            foreach ($this->wheres as $index => $where) {
                $whereColumn = trim($where['column']);
                if ($index > 0)
                    $sql .= " {$where['type']} ";
                $sql .= "`$whereColumn` {$where['operator']} {$where['value']}";
            }
        }
        return $sql;
    }
    /**
     * Helper method generate order by string
     */
    private function _getOrderByString(): string
    {
        $sql = '';
        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ';
            foreach ($this->orderBy as $index => $order) {
                $column = trim($order['column']);
                $direction = $order['direction'];
                if ($index > 0)
                    $sql .= ' ,';
                $sql .= "`$column` $direction";
            }
        }
        return $sql;
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




    private function __tableRequired()
    {
        if (!$this->table || empty($this->table))
            throw new \Exception("Table is not defined for database operation.");
    }
}

