<?php

namespace Core\Base;

use Core\Database\DB;
use Core\Utilities\Rex;

/**
 * @method DB table(string $table)
 * @method DB select(string|array $columns = '*')
 * @method DB where(string $column, mixed $operator, mixed $value = null)
 * @method DB orWhere(string $column, mixed $operator, mixed $value = null)
 * @method DB whereIn(string $column, array $valueList)
 * @method DB orWhereIn(string $column, array $valueList)
 * @method DB whereNotIn(string $column, array $valueList)
 * @method DB orWhereNotIn(string $column, array $valueList)
 * @method DB whereNot(string $column, string $value)
 * @method DB orWhereNot(string $column, string $value)
 * @method DB whereBetween(string $column, string|int|float $start, string|int|float $end)
 * @method DB orWhereBetween(string $column, string|int|float $start, string|int|float $end)
 * @method DB whereNotBetween(string $column, string|int|float $start, string|int|float $end)
 * @method DB orWhereNotBetween(string $column, string|int|float $start, string|int|float $end)
 * @method DB whereNull(string $column)
 * @method DB whereNotNull(string $column)
 * @method DB orderBy(string $column, string $direction)
 * @method DB join(string $table, string $condition, string $joinType = Operators::JOIN)
 * @method DB get()
 * @method array|null row()
 * @method array result()
 * @method array all($columns = '*')
 */
abstract class Model
{
    protected static string $dbGroup = 'default';
    protected static string $table;
    protected static string $primaryKey = 'id';
    protected static array $fillable = [];
    protected static string $createdField;
    protected static string $updatedField;
    private static array $dbInstances = [];

    // Non static properties
    protected array $attributes = [];
    public bool $exists = false;

    public function updateObject(Model $obj)
    {
        $this->attributes = $obj->attributes;
        $this->exists = $obj->exists;
    }
    public function __get($name)
    {
        foreach (['get' . ucfirst($name) . 'Attribute', 'get_' . $name . '_attribute'] as &$a)
            if (method_exists($this, method: $a))
                return $this->$a();
        // return $this->attributes[$name] ??= (method_exists($this, method: $name) ? $this->$name() : null);
        return $this->attributes[$name] ?? null;
    }
    public function __set($name, $value)
    {
        foreach (['set' . ucfirst($name) . 'Attribute', 'set_' . $name . '_attribute'] as &$a)
            if (method_exists($this, method: $a))
                return $this->$a($value);
        return $this->attributes[$name] = $value;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function put(array|object $data): void
    {
        if (is_array($data))
            $this->attributes = array_merge($this->attributes, $data);
        else if ($data instanceof Model)
            $this->updateObject($data);
        else
            $this->attributes = (array) $data;
    }
    public function save(): bool
    {
        if ($this->exists) {
            if (!isset($this->attributes[static::$primaryKey]))
                throw new \Exception("Primary key must exist in model object for the update!");
            return self::table()->updateById($this->attributes[static::$primaryKey], $this->attributes, static::$primaryKey);
        }
        return self::create($this->attributes) && ($this->exists = true);
    }

    public function delete(): bool
    {
        if (!$this->exists)
            throw new \Exception("Record doesn't exists in the database.");
        if (!isset($this->attributes[static::$primaryKey]))
            throw new \Exception("Primary key must exist in model object for the deletion!");
        return self::table()->deleteById($this->attributes[static::$primaryKey], static::$primaryKey);
    }



    public function toArray(): array
    {
        return $this->attributes;
    }


    /**
     * @return list<static>
     */
    public static function all(array|string $columns = '*'): array
    {
        return self::table()->all($columns);
    }


    /**
     * @return static|null
     */
    public static function find(int|string $id, string|array $columns = '*'): static|null
    {
        return self::table()->select($columns)->where(static::$primaryKey, $id)->get()->row() ?? null;
    }



    public static function create(array $data, bool $returnObject = false)
    {
        $inserData = [];
        foreach ($data as $column => &$value)
            if (in_array($column, static::$fillable))
                $inserData[$column] = $value;
        if (isset(static::$createdField))
            $inserData[static::$createdField] = Rex::now();
        if (isset(static::$updatedField))
            $inserData[static::$updatedField] = Rex::now();
        $return = self::table()->insert($inserData, returnId: $returnObject);
        return $returnObject ? self::find(id: $return) : $return;
    }

    public static function __callStatic($method, $args)
    {
        return self::table()->$method(...$args);
    }


    private static function table(): DB
    {
        $modelClass = get_called_class();
        if (!isset(self::$dbInstances[$modelClass]))
            self::$dbInstances[$modelClass] = db(static::$dbGroup, shared: false);
        return self::$dbInstances[$modelClass]->table(static::$table)
            ->setFetchMode([\PDO::FETCH_CLASS, $modelClass]);
    }

}