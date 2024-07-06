<?php

namespace Core\Database;

use InvalidArgumentException;
use PDO;
use PDOStatement;

class DB
{
    private PDO $pdo;
    private string $table = '';
    private string $columns = '';
    private string $wheres = "";
    private string $joins = "";
    private string $groupBy = "";
    private string $orderBy = "";
    private array $preparedData = [];
    private ?PDOStatement $selectStatement;
    private ?array $allowedFields = null;
    private array $fetchMode = [PDO::FETCH_ASSOC];



    private static array $sharedInstances = [];



    public function __construct(string $group = 'default')
    {
        $pdo = pdo_instance(group: $group);
        if (!$pdo)
            throw new \Exception("Got null instead of PDO Object.");
        $this->pdo = $pdo;
    }

    public static function getInstance(string $group = 'default', bool $shared = true): DB
    {
        if ($shared)
            return self::$sharedInstances[$group] ?? (self::$sharedInstances[$group] ??= new DB(group: $group));
        return new DB(group: $group);
    }

    public function resetBuilder()
    {
        $this->table = '';
        $this->columns = '';
        $this->wheres = "";
        $this->joins = "";
        $this->groupBy = "";
        $this->orderBy = "";
        $this->preparedData = [];
        $this->selectStatement = null;
        $this->fetchMode = [PDO::FETCH_ASSOC];
    }


    public function setFetchMode($mode): self
    {
        $this->fetchMode = $mode;
        return $this;
    }
    public function table(string $table): self
    {
        $this->resetBuilder();
        $this->table = $table;
        return $this;
    }


    public function select(string|array $columns = '*'): self
    {
        $this->columns .= (!empty($this->columns) ? ', ' : '') . $this->_getSelectColumnString($columns);
        return $this;
    }
    public function where(string $column, mixed $operator, mixed $value = null): self
    {
        if (func_num_args() == 2) {
            $value = $operator;
            $operator = Operators::EQUALS;
        }

        $this->handleWhere($column, $operator, $value);
        return $this;
    }
    public function orWhere(string $column, mixed $operator, mixed $value = null): self
    {
        if (func_num_args() == 2) {
            $value = $operator;
            $operator = Operators::EQUALS;
        }
        $this->handleWhere($column, $operator, $value, type: Operators::OR );
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
    public function whereNot(string $column, $value): self
    {
        $this->handleWhere(column: $column, operator: Operators::NOT_EQUALS, value: $value);
        return $this;
    }
    public function orWhereNot(string $column, $value): self
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
        $isFirst = empty($this->orderBy);
        $this->orderBy .= $isFirst ? ' ORDER BY ' : '';
        $this->orderBy .= !$isFirst ? ', ' : '';
        $this->orderBy .= " `$column` $direction ";
        return $this;
    }
    public function join(string $table, string $condition, string $joinType = Operators::JOIN): self
    {
        $this->joins .= " $joinType `$table` ON $condition ";
        return $this;
    }
    public function groupBy(string $groupBy): self
    {
        $this->groupBy = " GROUP BY $groupBy ";
        return $this;
    }

    public function get(): self
    {
        $this->__tableRequired();

        $fields = empty($this->columns) ? '*' : $this->columns;

        $sql = "SELECT $fields FROM `$this->table`";
        $sql .= $this->wheres;
        $sql .= $this->joins;
        $sql .= $this->orderBy;
        $sql .= $this->groupBy;
        // dd($sql);
        $this->selectStatement = $this->pdo->prepare($sql);
        $this->selectStatement->execute($this->preparedData);
        $this->selectStatement->setFetchMode(...$this->fetchMode);

        return $this;
    }


    /**
     * @return array|null
     */
    public function row()
    {
        if ($this->selectStatement) {
            $data = $this->selectStatement->fetch();
            if (!$data || ($data === false))
                return null;
        }
        if (is_object($data) && property_exists($data, 'exists'))
            $data->exists = true;
        return $data;
    }



    /**
     * @return array[]
     */
    public function result(): array
    {
        $data = [];
        if ($this->selectStatement)
            $data = $this->selectStatement->fetchAll();
        if (!empty($data) && is_object($data[0]) && property_exists($data[0], 'exists'))
            array_map(fn($dt) => $dt->exists = true, $data);
        return $data;
    }


    /**
     * Returns all the records from the table
     */
    public function all($columns = '*'): array
    {
        $this->__tableRequired();
        return $this->get()->result();
    }

    public function findById($id, string|array $columns = '*', string $primaryKey = 'id')
    {
        $this->__tableRequired();
        return $this->select($columns)->where($primaryKey, $id)->get()->row() ?? null;
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

        $columns = $this->_getSelectColumnString(array_keys($data));
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
        $this->_setPreparedData(array_reverse(array_values($data)), prepend: true);
        $setString = $this->_getUpdateSetString(data: $data);
        return $this->pdo->prepare("UPDATE `$this->table` SET $setString $this->wheres")->execute($this->preparedData);
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
        return $this->pdo->prepare("DELETE FROM `$this->table` $this->wheres")->execute($this->preparedData);
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




    public function transaction(callable $func)
    {
        $this->pdo->beginTransaction();
        try {
            $func(); // use only this pdo inside the transaction call back
            if (!$this->pdo->inTransaction())
                throw new \Exception("Transaction ended prematurely");
            $this->pdo->commit();
        } catch (\Exception $e) {
            if ($this->pdo->inTransaction())
                $this->pdo->rollBack();
            throw $e;
        }
    }




    // *****************************************************************
    // PRIVATE METHODS
    // *****************************************************************



    private function handleWhere(string $column, string $operator, $value = null, string $type = Operators::AND )
    {
        $column = trim($column);
        $operator = $this->_getWhereOperator($operator, $value);
        $placeholder = Operators::getWherePlaceholder(operator: $operator, value: $value);
        $isFirst = empty($this->wheres);

        $this->wheres .= $isFirst ? " WHERE " : '';
        $this->wheres .= !$isFirst ? $type : '';
        $this->wheres .= " $column $operator $placeholder ";

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


    private function _getSelectColumnString(string|array $columns): string
    {
        if (is_string($columns))
            $columns = explode(',', $columns);
        $columns = array_map('trim', $columns);
        return implode(', ', $columns);
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





    private function __tableRequired()
    {
        if (!$this->table || empty($this->table))
            throw new \Exception("Table is not defined for database operation.");
    }
}

