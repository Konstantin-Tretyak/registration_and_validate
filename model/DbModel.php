<?php

abstract class DbModel
{
    protected static $table;
    protected static $conn;
    protected static $primaryKey = 'id';

    public static $observers = [];

    public static function setConnection(PDO $conn)
    {
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$conn = $conn;
    }

    public static function getConnection()
    {
        return self::$conn;
    }

    public function fill(array $attributes)
    {
        foreach ($attributes as $attribute => $value)
        {
            $this->$attribute = $value;
        }

        return $this;
    }

    public function __construct(Array $attributes = [])
    {
        $this->fill($attributes);
    }

    public static function query($value='')
    {
        ///???Что значит static::class
        return new QueryBuilder(self::$conn, [self::getTableName() => '*'], static::class);
    }

    public static function getDbName()
    {
        ///???fetchColumn - для чего
        return self::$conn->query('select database()')->fetchColumn();
    }

    public static function getTableName()
    {
        $currentClass = get_called_class();
        return $currentClass::$table;
    }

    public static function getPrimaryKeyName()
    {
        $currentClass = get_called_class();
        return $currentClass::$primaryKey;
    }

    public function getIdName()
    {
        return isset($this->{self::getPrimaryKeyName()}) ? $this->{self::getPrimaryKeyName()} : null;
    }

    public function id()
    {
        return $this->getIdName();
    }

    public function getColumnNames()
    {
        $sql = 'select column_name from information_schema.columns where table_schema="'.self::getDbName().'" and table_name="'.self::getTableName().'"';
        $stmt = self::$conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function toArray()
    {
        $columns = self::getColumnNames();
        $result = [];
        foreach ($columns as $column)
        {
            $result[$column] = isset($this->$column) ? $this->$column : null;
        }

        return $result;
    }


    public static function find($id)
    {
        return self::query()->where(self::getPrimaryKeyName().' = ?', [$id])->first();
    }

    public static function create(Array $attributes)
    {
        $currentClass = get_called_class();
        $new_object = new $currentClass;
        $new_object->fill($attributes)->save();
        return $new_object;
    }

    public function update(Array $attributes)
    {
        $this->original_data = clone $this;
        $this->fill($attributes)->save();
        return $this;
    }

    public static function delete($deleting, $number)
    {
        $sql = sprintf("DELETE FROM %s WHERE %s = ?",
                        self::getTableName(),
                        $deleting);

        self::$conn->prepare($sql)->execute([$number]);
        //$this->{self::getPrimaryKeyName()} = null;
    }

    public function save()
    {
        if ($this->isNew())
        {
            $this->insertQuery();
            $this->afterCreate();
        }
        else
        {
            $this->updateQuery();
            $this->afterUpdate();
        }
        return $this;
    }

    public function isNew()
    {
        return empty($this->getIdName());
    }

    protected function insertQuery()
    {
        $attributes = $this->toArray();
        ///Зачем unset
        unset($attributes[self::getPrimaryKeyName()]);

        $columns = [];
        $bindings = [];
        $values = [];
        foreach ($attributes as $column => $value)
        {
            if ($column == "update_date" or $column == "create_date")
            {
                $value = date('Y-m-d h:i:s');
            }
            $columns[] = sprintf('`%s`', $column);
            ///Может быть $bindings - лишний
            $bindings[] = "?";
            $values[] = $value;
        }

        $sql = sprintf("INSERT INTO `%s` (%s) VALUES (%s)", self::getTableName(), join(', ', $columns), join(', ', $bindings));
        self::$conn->prepare($sql)->execute($values);
        $this->{self::getPrimaryKeyName()} = self::$conn->lastInsertId();
    }

    protected function updateQuery()
    {
        $attributes = $this->toArray();
        ///Зачем unset
        unset($attributes[self::getPrimaryKeyName()]);

        $updates = [];
        $values = [];

        foreach ($attributes as $column => $value)
        {
            if ($column == "update_date")
            {
                $value = date('Y-m-d h:i:s');
            }
            $updates[] = sprintf('%s = ?', $column);
            $values[] = $value;
        }

        $values[] = $this->getIdName();

        $sql = sprintf("UPDATE %s SET %s WHERE %s = ?", self::getTableName(), join(',', $updates), self::getPrimaryKeyName());
        self::$conn->prepare($sql)->execute($values);
    }

    public function hasMany($slaveClass, $slaveForeignKey, $localKey)
    {
        $localTable = self::getTableName();
        $slaveTable = $slaveClass::getTableName();
        $query = new QueryBuilder(self::$conn, [$localTable => '*',$slaveTable => '*'], $slaveClass);
        return $query->where("$slaveTable.$slaveForeignKey = $localTable.$localKey  AND `$localTable`.`$localKey` = ? ", [$this->{$localKey}]); //???Здесь не понятно
    }

    public function hasManyThrough($slaveClass, $middleClass, $middleToSlaveKey, $middleToLocalKey)
    {
        $slaveTable = $slaveClass::getTableName();
        $middleTable = $middleClass::getTableName();

        $query = new QueryBuilder(self::$conn, [$slaveTable => '*', $middleTable => null], $slaveClass);
        $query->where("$slaveTable.id = $middleTable.$middleToSlaveKey AND $middleTable.$middleToLocalKey = ?", [$this->id()]);

        return $query;
    }

    public function belongsTo($masterClass, $localKey, $masterForeignKey)
    {
        // TODO: automatically guess $masterForeignKey and $localKey (guess standard column names)
        $localTable = self::getTableName();
        $masterTable = $masterClass::getTableName();
        $query = new QueryBuilder(self::$conn, [$masterTable => '*', $localTable => '*'], $masterClass);
        return $query->where("`$localTable`.`$localKey` = `$masterTable`.`$masterForeignKey` AND `$masterTable`.`$masterForeignKey` = ?", [$this->{$localKey}]);
    }

    public static function afterCreateObserver($observer)
    {
        $currentClass = get_called_class();
        self::$observers[$currentClass]['afterCreate'][] = $observer;
    }

    public static function afterUpdateObserver($observer)
    {
        $currentClass = get_called_class();
        self::$observers[$currentClass]['afterUpdate'][] = $observer;
    }

    public function afterCreate()
    {
        $this->dispatchEvent('afterCreate');
    }

    public function afterUpdate()
    {
        $this->dispatchEvent('afterUpdate');
    }

    protected function dispatchEvent($event)
    {
        $currentClass = get_called_class();
        if (isset(self::$observers[$currentClass][$event]))
        {
            $observers = self::$observers[$currentClass][$event];
            foreach ($observers as $observer)
            {
                $observer($this);
            }
        }
    }

}