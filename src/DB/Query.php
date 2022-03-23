<?php

namespace WebXID\EDMo\DB;

use WebXID\EDMo\Bugtracker;
use PDO;
use WebXID\EDMo\DB;
use PDOException;
use InvalidArgumentException;

/**
 * Class Query
 * @see \WebXID\EDMo\DB
 *
 * @package DB
 */
class Query
{
    const UPDATE_TYPE = 'update';
    const INSERT_TYPE = 'insert';
    const REPLACE_TYPE = 'replace';
    const DELETE_TYPE = 'delete';

    /**
     * @var Query[]
     */
    private static $connections = false;

    //Connection session
    private $connection_name = false;

    /**
     * @var \PDOStatement|null
     */
    private $prepared_statement = null;

    /**
     * @var PDO
     */
    private $current_connection = null;

    /** @var int */
    private $transactions_count = 0;

    //Temp properties
    private $query = false;
    private $where = false;

    /**
     * @var bool|array
     */
    private $binds = false;
    private $no_duplication = false;
    private $query_type = false;

    /**
     * @param string $connection_name
     * @param array $db_settings
     * @param bool $force - pass TRUE to refresh connection
     *
     * @return Query
     */
    static public function init($connection_name, array $db_settings, bool $force = false)
    {
        if (isset(self::$connections[$connection_name]) && self::$connections[$connection_name] instanceof Query && !$force) {
            return self::$connections[$connection_name];
        }

        if (!is_string($connection_name) || empty($connection_name)) {
            throw new InvalidArgumentException('Invalid $connection_name');
        }

        $object = new self();
        $object->connection_name = $connection_name;

        if (
            empty($db_settings)
            || empty($db_settings['host'])
            || empty($db_settings['port'])
            || empty($db_settings['user'])
            || !isset($db_settings['pass'])
            || empty($db_settings['db_name'])
            || !isset($db_settings['use_persistent_connection'])
        ) {
            throw new \InvalidArgumentException('Invalid "' . $connection_name . '" DB connection settings');
        }

        $host = $db_settings['host'];
        $port = $db_settings['port'];
        $user = $db_settings['user'];
        $pass = $db_settings['pass'];
        $db_name = $db_settings['db_name'];
        $use_persistent_connection = $db_settings['use_persistent_connection'];

        if (!isset($db_settings['charset'])) {
            $db_settings['charset'] = 'utf8';
        }

        $charset = $db_settings['charset'];

        $dsn = "mysql:host={$host};port={$port};dbname={$db_name}";
        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT => $use_persistent_connection,
        ];

        $object->current_connection = new PDO($dsn, $user, $pass, $opt);
        $object->current_connection->exec("SET NAMES {$charset}");

        return self::$connections[$connection_name] = $object;
    }

    private function __construct() {}

    public function __get($property_name)
    {
        switch ($property_name) {
            default:
                throw new \InvalidArgumentException("Property '{$property_name}' does not exist");
        }
    }

    /**
     * Begin MySQL Transaction
     *
     * @return Query
     */
    public function beginTransaction()
    {
        if ($this->transactions_count === 0) {
            $this->current_connection->beginTransaction();
        }

        $this->transactions_count ++;

        return $this;
    }

    /**
     * Commit MySQL Transaction
     *
     * @return Query
     */
    public function commitTransaction()
    {
        if ($this->transactions_count === 1) {
            $this->current_connection->commit();
        }

        $this->transactions_count --;

        return $this;
    }

    /**
     * Rollback MySQL Transaction
     *
     * @return Query
     */
    public function rollbackTransaction()
    {
        $this->current_connection->rollBack();
        $this->transactions_count = 0;

        return $this;
    }

    /**
     * Set DB query
     *
     * @param string $query
     *
     * @return Query
     *
     * @throws \InvalidArgumentException
     *          if DB connection is not exists
     **/
    public function query($query)
    {
        if (!is_string($query) || empty($query)) {
            throw new \InvalidArgumentException('$query must be not empty string');
        }

        $this->query = $query;

        return $this;
    }

    /**
     * Make Update Query
     *
     * @param string $table - table name or JOINed tables, which has the same column names
     *
     * @return Query
     *
     * @throws \InvalidArgumentException
     *            if $table is empty or not string
     **/
    public function update($table)
    {
        if (!is_string($table) || empty($table)) {
            throw new \InvalidArgumentException('$table must be not empty string');
        }

        $this->query_type = self::UPDATE_TYPE;
        $this->table = $table;

        return $this;
    }

    /**
     * Make Insert Query
     *
     * @param string $table - table name or JOINed tables, which has the same column names
     * @param mixed $action_on_duplication - set DB::UPDATE_DUPLICATE , if needs to update available rows (works through 'ON DUPLICATE KEY')
     *
     * @return Query
     *
     * @throws \InvalidArgumentException
     *            if $table is empty or not string
     **/
    public function insert($table, $action_on_duplication = DB::DUPLICATE_ERROR)
    {
        if (!is_string($table) || empty($table)) {
            throw new \InvalidArgumentException('$table must be not empty string');
        }

        $this->query_type = self::INSERT_TYPE;
        $this->table = $table;
        $this->no_duplication = $action_on_duplication;

        return $this;
    }

    /**
     * Make Repleace Query
     *
     * @param string $table - table name or JOINed tables, which has the same column names
     *
     * @return Query
     *
     * @throws \InvalidArgumentException
     *            if $table is empty or not string
     **/
    public function replace($table)
    {
        if (!is_string($table) || empty($table)) {
            throw new \InvalidArgumentException('$table must be not empty string');
        }

        $this->query_type = self::REPLACE_TYPE;
        $this->table = $table;

        return $this;
    }

    /**
     * Make Delete Query
     *
     * @param string $table - table name or JOINed tables
     *
     * @return Query
     *
     * @throws \InvalidArgumentException
     *            if $table is empty or not string
     **/
    public function delete($table)
    {
        if (!is_string($table) || empty($table)) {
            throw new \InvalidArgumentException('$table must be not empty string');
        }

        $this->query_type = self::DELETE_TYPE;
        $this->query = "    DELETE FROM {$table} ";

        return $this;
    }

    /**
     * Set values, which needs to send to DB
     *
     * @param array $values
     * @param string $column_name_prifix - set in parameter, if using prefix of table column names
     *
     * @return Query
     *
     * @throws \InvalidArgumentException
     *             if used not for allowed methods: update(), insert(), replace()
     *            if $values is empty or not array
     *             if $column_name_prifix is not string
     *
     **/
    public function values($values, $column_name_prifix = '')
    {
        if (!in_array($this->query_type, [self::UPDATE_TYPE, self::INSERT_TYPE, self::REPLACE_TYPE])) {
            throw new \BadMethodCallException('Method values() has to be called after methods: update(), insert(), replace()');
        }

        if (empty($values) || !is_array($values)) {
            throw new \InvalidArgumentException('$values must be not empty array');
        }

        if (!is_string($column_name_prifix)) {
            throw new \InvalidArgumentException('$column_name_prifix must be string');
        }

        switch ($this->query_type) {
            case self::UPDATE_TYPE:
                $this->setUpdateValues($values, $column_name_prifix);

                break;

            default:
                $this->setInsertReplaceValues($values, $column_name_prifix);

                break;
        }

        return $this;
    }

    /**
     * Pripare values for Update query
     *
     * @param array $values
     * @param string $column_name_prifix - set in parameter, if using prefix of table column names
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     *             if column name ($key) is empty or not string
     *
     **/
    private function setUpdateValues($values, $column_name_prifix = '')
    {
        $update_query = '';
        $rendom_str = rand(4, 6);
        $i = 0;

        foreach ($values AS $key => $value) {
            if (!is_string($key) || $key == '') {
                throw new \InvalidArgumentException('Keys of $values array must be not empty strings for update()');
            }

            if ($i > 0) {
                $update_query .= ',';
            }

            $update_query .= " `" . str_replace('.', '`.`', $column_name_prifix . $key) . "` = :update_{$rendom_str}_val_{$i} ";
            $update_vars[":update_{$rendom_str}_val_{$i}"] = $value;

            $i++;
        }

        $this->query = "    UPDATE {$this->table} SET {$update_query} ";

        $this->binds($update_vars);
    }

    /**
     * Prepare values for Insert or Replace query
     *
     * @param array $values
     * @param string $column_name_prifix - set in parameter, if using prefix of table column names
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     *             if column name ($key) is empty or not string
     *
     **/
    private function setInsertReplaceValues($values, $column_name_prifix = '')
    {
        $update_query = $columns = $col_values = '';
        $insert_vars = [];
        $rendom_str = rand(4, 6);
        $i = 0;

        foreach ($values AS $key => $value) {
            if (!is_string($key) || $key == '') {
                throw new \InvalidArgumentException('Keys of $values array must be not empty strings for ' . $this->query_type . '()');
            }

            if ($i > 0) {
                $columns .= ', ';
                $col_values .= ',';
                $update_query .= ', ';
            }

            $column_name = ' `' . str_replace('.', '`.`', $column_name_prifix . $key) . '` ';
            $columns .= $column_name;
            $col_values .= " :insert_{$rendom_str}_val_{$i} ";

            $update_query .= " {$column_name} = :insert_{$rendom_str}_val_{$i} ";
            $insert_vars[":insert_{$rendom_str}_val_{$i}"] = $value;

            $i++;
        }

        switch ($this->query_type) {
            case self::INSERT_TYPE:
                switch ($this->no_duplication) {
                    case DB::DUPLICATE_UPDATE:
                        $table = explode('.', $this->table);

                        if (count($table) > 1) {
                            $table = $table[count($table)-1];
                        } else {
                            $table = $table[0];
                        }

                        $ai_column_name = (clone $this)->query('
                            SELECT k.COLUMN_NAME
                                FROM information_schema.table_constraints t
                                    LEFT JOIN information_schema.key_column_usage k USING(constraint_name, table_schema, table_name)
                                WHERE t.constraint_type = "PRIMARY KEY"
                                    AND t.table_schema=DATABASE() 
                                    AND t.table_name=:table_name
                            ')
                            ->binds(':table_name', str_replace('`', '', $table))
                            ->execute()
                            ->fetchValue('COLUMN_NAME');

                        $this->query = "    INSERT INTO {$this->table} ({$columns}) VALUES ({$col_values}) ON DUPLICATE KEY UPDATE {$update_query}" .
                            (
                                $ai_column_name
                                    ? ", {$ai_column_name}=LAST_INSERT_ID({$ai_column_name})"
                                    : ''
                            ) .
                            ";";

                        break;

                    case DB::DUPLICATE_IGNORE:
                        $this->query = "    INSERT IGNORE INTO {$this->table} ({$columns}) VALUES ({$col_values});";
                        break;

                    default:
                        $this->query = "    INSERT INTO {$this->table} ({$columns}) VALUES ({$col_values});";
                }

                break;

            case self::REPLACE_TYPE:
                $this->query = "    REPLACE INTO {$this->table} ({$columns}) VALUES ({$col_values});";

                break;
        }

        $this->binds($insert_vars);
    }

    /**
     * Set "WHERE" conditions
     *
     * @param string $conditions
     *
     * @return Query
     *
     * @throws \InvalidArgumentException
     *            if used not for allowed methods: update(), delete()
     *             if "WHERE" conditions exists before, for current query
     **/
    public function where($conditions = '')
    {
        if (!in_array($this->query_type, [self::UPDATE_TYPE, self::DELETE_TYPE])) {
            throw new \BadMethodCallException('Method where() allow to use only after methods: update(), insert(), repleace(), delete()');
        }

        if (!empty($conditions)) {
            if ($this->where) {
                throw new \InvalidArgumentException('Current query ' . $this->query_type . '() already using "WHERE" conditions');
            }

            $this->where = " WHERE {$conditions} ";
        }

        return $this;
    }

    /**
     * Prepare binds array
     *
     * @param array|string $binds_array
     *
     * @return Query
     *
     * @throws \InvalidArgumentException
     *             if $binds_array is empty or not array
     *             if bind placeholder is empty or not string
     *            if bind value is not scalar
     **/
    public function binds($binds_array, $param = '')
    {
        if (is_string($binds_array)) {
            $this->binds([$binds_array => $param]);
        } elseif (is_array($binds_array) && !empty($binds_array)) {
            foreach ($binds_array AS $placeholder => $bind_value) {
                if (
                    (!is_string($placeholder) || empty($placeholder))
                    && !is_int($placeholder) //ToDo: remove after DataBase refactoring
                ) {
                    throw new \InvalidArgumentException('Invalide bind placeholder - ' . $placeholder . ' should be not empty string');
                }

                if (is_string($placeholder) && $placeholder[0] != ':') {
                    $placeholder = ':' . $placeholder;
                } elseif (is_int($placeholder)) { //ToDo: remove after DataBase refactoring
                    $placeholder ++;
                }

                if (is_scalar($bind_value) || $bind_value == null) {
                    $this->binds[$placeholder] = $bind_value;
                } elseif (is_array($bind_value)) {
                    if (empty($bind_value)) {
                        throw new \InvalidArgumentException('Array bind value has to be not empty array');
                    }

                    $binds_keys = [];

                    foreach (array_values($bind_value) as $key => $val) {
                        if (!is_scalar($val)) {
                            throw new \InvalidArgumentException('Invalide bind value - ' . $placeholder . ' -> ' . $key . ' should be scalar');
                        }

                        $this->binds[$placeholder . $key] = $val;
                        $binds_keys[] = $placeholder . $key;
                    }

                    if (!empty($binds_keys)) {
                        $this->query = str_replace($placeholder, implode(',', $binds_keys), $this->query);
                        $this->where = str_replace($placeholder, implode(',', $binds_keys), $this->where);
                    }
                } else {
                    throw new \InvalidArgumentException('Invalide bind value - ' . $placeholder . ' should be scalar or array');
                }
            }
        } else {
            throw new \InvalidArgumentException('Invalide parameter - $binds_array should be not empty array');
        }

        return $this;
    }

    /**
     * Execute prepared query
     *
     * @return Query
     **/
    public function execute()
    {
        if ($this->where) {
            $this->query .= ' ' . $this->where;
        }

        $this->prepared_statement = $this->current_connection->prepare($this->query);

        if (!empty($this->binds)) {
            foreach ($this->binds as $placeholder => $value) {
                if (is_bool($value)) {
                    $value = (int) $value;
                }

                $this->prepared_statement->bindValue($placeholder, $value);
            }
        }

        try {
            $this->prepared_statement->execute();
        } catch (PDOException $e) {
            $this->reset();

            throw $e;
        }

        $this->reset();

        return $this;
    }

    /**
     * Reset temp properties
     */
    private function reset()
    {
        $this->where = false;
        $this->query = false;
        $this->binds = false;
        $this->query_type = false;
        $this->no_duplication = false;
    }

    /**
     * Return row count
     *
     * @return mixed
     */
    public function rowCount()
    {
        return $this->prepared_statement->rowCount();
    }

    /**
     * Get row array
     *
     * @param string $style
     * @param int $cursor_orientation
     * @param int $cursor_offset
     *
     * @return mixed
     */
    public function fetch($style = 'FETCH_ASSOC', $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
        return $this->prepared_statement->fetch(constant('PDO::' . $style), $cursor_orientation, $cursor_offset);
    }

    /**
     * Get value of 1 column
     *
     * @param string|int $key
     *
     * @return mixed|null
     *
     * @throws \InvalidArgumentException
     *          if column with name $key was not found in request result
     **/
    public function fetchValue($key = 0)
    {
        $result = $this->fetch('FETCH_BOTH');

        if (!$result) {
            return null;
        }

        if (!is_string($key) && !is_int($key)) {
            throw new \InvalidArgumentException('Parameter $key should be int or not empty string');
        } elseif (!array_key_exists($key, $result)) {
            throw new \InvalidArgumentException('Column "' . $key . '" was not found in request result');
        }

        return $result[$key];
    }

    /**
     * Get rows array
     *
     * @param mixed $key - name or number of column, which value needs to be key of arrays elements
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     *          if column with name $key was not found in request result
     **/
    public function fetchArray($key = null)
    {
        $result = [];

        if (is_string($key)) {
            $tmp_result = $this->prepared_statement->fetchAll();

            if (!empty($tmp_result)) {
                if (!array_key_exists($key, $tmp_result[0])) {
                    throw new \InvalidArgumentException('Column "' . $key . '" was not found in request result');
                }

                foreach ($tmp_result as $cval) {
                    $result[$cval[$key]] = $cval;
                }

                unset($cval);
            }

            unset($tmp_result);
        } elseif (is_int($key)) {
            $result = $this->prepared_statement->fetchAll(null, $key);
        } else {
            $result = $this->prepared_statement->fetchAll();
        }

        return $result;
    }

    /**
     * Get rows array
     *
     * @link http://php.net/manual/en/pdostatement.fetchall.php
     */
    public function fetchAll($style = false, $fetch_argument = null, $ctor_args = [])
    {
        if (is_string($style)) {
            $style = constant('PDO::' . $style);
        } else {
            $style = null;
        }

        if ($fetch_argument && !empty($ctor_args)) {
            $result = $this->prepared_statement->fetchAll($style, $fetch_argument, $ctor_args);
        } elseif ($fetch_argument) {
            $result = $this->prepared_statement->fetchAll($style, $fetch_argument);
        } else {
            $result = $this->prepared_statement->fetchAll($style);
        }

        return $result;
    }

    /**
     * Get value of column $value
     *
     * @param string $value - column name, which value needs to be value of arrays element
     * @param string|int|false $key - column name, which value needs to be key of arrays element
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     *          if parameter $value is not string or empty
     *          if column with name $value was not found in request result
     *          if column with name $key was not found in request result
     */
    public function fetchColumn($value, $key = false)
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException('Parameter $value should be not empty string');
        }

        $result = [];

        $tmp_result = $this->prepared_statement->fetchAll();

        if (empty($tmp_result)) {
            return $result;
        }

        if (!array_key_exists($value, $tmp_result[0])) {
            throw new \InvalidArgumentException('Column "' . $value . '" was not found in request result');
        }

        if ($key && !array_key_exists($key, $tmp_result[0])) {
            throw new \InvalidArgumentException('Column "' . $key . '" was not found in request result');
        }

        if (!$key) {
            $result = array_column($tmp_result, $value);
        } else {
            foreach ($tmp_result as $cval) {
                $result[$cval[$key]] = $cval[$value];
            }
        }

        return $result;
    }

    /**
     * Returns auto increment ID, which was inserted into DB in the last time
     *
     * @return int
     */
    public function lastInsertId()
    {
        return $this->current_connection->lastInsertId();
    }

    /**
     * @return string
     */
    public function isFailed()
    {
        return $this->prepared_statement->errorCode() !== '00000';
    }

    /**
     * Closes the cursor, enabling the statement to be executed again.
     * @see http://php.net/manual/en/pdostatement.closecursor.php
     *
     * @return $this
     */
    public function close()
    {
        $this->prepared_statement->closeCursor();

        return $this;
    }

    /**
     * Renurns DB Request Error data
     *
     * @param null|string|int $element_key
     *
     * @return array
     */
    public function getErrorsList($element_key = null)
    {
        if ($element_key === null) {
            return $this->prepared_statement->errorInfo();
        }

        $error_data = $this->prepared_statement->errorInfo();

        if (!isset($error_data[$element_key])) {
            throw new \InvalidArgumentException('Is no Error data for $element_key = ' . $element_key);
        }

        return $error_data[$element_key];
    }

    /**
     * Clean / destroy connections
     */
    static public function clean($connection_name)
    {
        if ($connection_name) {
            if (isset(self::$connections[$connection_name])) {
                unset(self::$connections_config[$connection_name], self::$connections[$connection_name]);
            }
        } else {
            self::$connections_config = [];
            self::$connections = false;
        }
    }
}
