<?php

class QueryBuilder
{
    protected $conn;
    protected $query;
    protected $bindings = array();

    public function __construct($conn, $columnsToSelect, $resultClass = null)
    {
        $this->conn = $conn;

        $_columns = [];
        foreach ($columnsToSelect as $table => $columns)
        {
            if (empty($columns)) continue; 

            if ($columns == '*')
            {
                $_columns[] = "$table.*";
            }
            else
            {
                foreach ($columns as $column)
                {
                    $_columns[] = "$table.$column";
                }
            }
        }
            $this->query = "select ".join(',', $_columns)." from ".join(',',array_keys($columnsToSelect));
            ///???Что значит stdClass
            $this->resultClass = empty($resultClass) ? 'stdClass' : $resultClass;
    }

    public function all()
    {
        $statement = $this->conn->prepare($this->query);
        $statement->execute($this->bindings);

        $result = $statement->fetchAll(PDO::FETCH_CLASS, $this->resultClass);
        return $result;
    }

    public function first()
    {
        $results = $this->limit(1)->all();
        return $results ? $results[0] : null;
    }

    public function limit($limit)
    {
        $this->query .= " LIMIT $limit";
        return $this;
    }

    public function where($condition, $bindings)
    {
        ///???? Тут ищет первый попавшийся WHERE с учетом регитсра.
        ///???? Можна ли таблицу называть WHERE, что бы не было совпадений.
        if (strpos($this->query, "WHERE") )
        {
            $this->query .= " AND $condition";
        }
        else
        {
            $this->query .= " WHERE $condition";
        }

        $this->bindings = array_merge($this->bindings, $bindings);
        return $this;
    }

    public function or_where($condition, $bindings)
    {
        if (!strpos($this->query, "WHERE") )
        {
            $this->query .= " WHERE $condition";
        }
        else
        {
            $this->query .= " OR $condition";
        }

        $this->bindings = array_merge($this->bindings, $bindings);
        return $this;
    }

    public function offset($offset, $limit)
    {
        $this->query .= " LIMIT $offset, $limit";

        return $this;
    }

    public function order_by($order, $direction="down")
    {
        if($direction=="up")
            $direction="DESC";
        else
            $direction="ASC";

        $this->query .= " ORDER BY $order $direction";
        return $this;
    }

    public function count()
    {
        return count( $this->all() );
    }
}