<?php
    class DbValidator extends \Illuminate\Validation\Validator
    {
        public function __construct($connection, $translator, $data, $rules, $messages, $customAttributes)
        {
            $this->connection = $connection;
            return parent::__construct($translator, $data, $rules, $messages, $customAttributes);
        }

        public function validateExists($attribute, $value, $parameters)
        {
            $this->requireParameterCount(2, $parameters, 'exists');
            $table = $parameters[0];
            $column = $parameters[1];

            $result = $this->_query("select count(*) as value_count from `$table` where `$column` = ?", [$value]);
            return ($result['value_count'] > 0);
        }

        public function validateUnique($attribute, $value, $parameters)
        {
            $this->requireParameterCount(2, $parameters, 'exists');
            $table = $parameters[0];
            $column = $parameters[1];

            $result = $this->_query("select count(*) as value_count from `$table` where `$column` = ?", [$value]);
            return ($result['value_count'] == 0);
        }

        public function _query($query, $bindings)
        {
            $statement = $this->connection->prepare($query);
            $statement->execute($bindings);
            return $statement->fetch();
        }
    }