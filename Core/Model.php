<?php

namespace Core;

use Core\Database\DB;
use Core\Utilities\Rex;

/**
 * @method QueryBuilder table(string $table)
 * @method QueryBuilder select(string|array $columns = '*')
 * @method QueryBuilder where(string $column, mixed $operator, mixed $value = null)
 * @method QueryBuilder orWhere(string $column, mixed $operator, mixed $value = null)
 * @method QueryBuilder whereIn(string $column, array $valueList)
 * @method QueryBuilder orWhereIn(string $column, array $valueList)
 * @method QueryBuilder whereNotIn(string $column, array $valueList)
 * @method QueryBuilder orWhereNotIn(string $column, array $valueList)
 * @method QueryBuilder whereNot(string $column, string $value)
 * @method QueryBuilder orWhereNot(string $column, string $value)
 * @method QueryBuilder whereBetween(string $column, string|int|float $start, string|int|float $end)
 * @method QueryBuilder orWhereBetween(string $column, string|int|float $start, string|int|float $end)
 * @method QueryBuilder whereNotBetween(string $column, string|int|float $start, string|int|float $end)
 * @method QueryBuilder orWhereNotBetween(string $column, string|int|float $start, string|int|float $end)
 * @method QueryBuilder whereNull(string $column)
 * @method QueryBuilder whereNotNull(string $column)
 * @method QueryBuilder orderBy(string $column, string $direction)
 * @method QueryBuilder get()
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
    protected array $attributes = [];
    private static array $dbInstances = [];

    // Non static properties
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
        $obj = self::create($this->attributes, true);
        $this->put($obj);
        return !!$obj;
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
    public static function find(int|string $id): static|null
    {
        return self::table()->findById($id, static::$primaryKey);
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


    public static function table(): DB
    {
        $modelClass = get_called_class();
        if (!isset(self::$dbInstances[$modelClass]))
            self::$dbInstances[$modelClass] = db(static::$dbGroup, shared: false);
        return self::$dbInstances[$modelClass]->table(static::$table)
            ->setFetchMode([\PDO::FETCH_CLASS, $modelClass]);
    }
}