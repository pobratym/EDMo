<?php

namespace WebXID\EDMo;

use InvalidArgumentException;
use LogicException;

/**
 * Class DB
 *
 * @package WebXID\EDMo
 */
class DB
{
    const DEFAULT_CONNECTION_NAME = 'default';

    const DUPLICATE_UPDATE = 'update on duplicate key';
    const DUPLICATE_IGNORE = 'ignore on duplicate key';
    const DUPLICATE_ERROR = 'throw error on duplicate key';

    ## SQL types for exporting DB table
    const TYPE_INSERT = 'sql_type_insert';
    const TYPE_INSERT_IGNORE = 'sql_type_insert_ignore';
    const TYPE_UPDATE = 'sql_type_update';
    const TYPE_REPLACE = 'sql_type_replace';

    private static $current_connection_name = false;
    private static $connections_config = [];

    /**
     * Returning Name of last connection
     *
     * @return string
     **/
    public static function getLastConnectionName()
    {
        if (!is_string(self::$current_connection_name)) {
            throw new LogicException('Invalid self::$current_connection_name');
        }

        return self::$current_connection_name;
    }

    /**
     * @param array $connection_configs
     * [
     *         conniction_name => [
     *             'host' => string,
     *             'port' => string,
     *             'user' => string,
     *             'pass' => string,
     *             'db_name' => string,
     *             'use_persistent_connection' => bool,
     *             'charset' => string, // optional
     *         ],
     *         ...
     * ]
     */
    public static function addConfig(array $connection_configs)
    {
        if (!$connection_configs) {
            throw new InvalidArgumentException('Invalid $config');
        }

        foreach ($connection_configs as $name => $connection) {
            if (
                is_string($name)
                && !empty($name)
                && !empty($connection)
                && !empty($connection['host'])
                && !empty($connection['port'])
                && !empty($connection['user'])
                && isset($connection['pass'])
                && !empty($connection['db_name'])
                && isset($connection['use_persistent_connection'])
                && (!isset($connection['charset']) || !empty($connection['charset']))
            ) {
                continue;
            }

            throw new InvalidArgumentException('Invalid "' . $name . '" DB connection config');
        }

        self::$connections_config = $connection_configs + self::$connections_config;
    }

    /**
     * @param string|null $connection_name
     */
    public static function cleanConfig($connection_name = null)
    {
        if (
            !is_null($connection_name)
            && (!is_string($connection_name) || empty($connection_name))
        ) {
            throw new InvalidArgumentException('Invalid $connection_name');
        }

        if ($connection_name === null) {
            self::$connections_config = [];

            return;
        }

        unset(self::$connections_config[$connection_name]);
    }

    /**
     * Make connection to Data Base
     *
     * @param string|false $connection_name - connection name
     * @param bool $force - pass TRUE to refresh connection
     *
     * @return DB\Query
     **/
    public static function connect($connection_name = false, bool $force = false)
    {
        if (!$connection_name) {
            $connection_name = self::DEFAULT_CONNECTION_NAME;
        }

        self::$current_connection_name = $connection_name;

        if (!isset(self::$connections_config[self::$current_connection_name])) {
            throw new InvalidArgumentException('Invalid DB connection');
        }

        return DB\Query::init(self::$current_connection_name, self::$connections_config[self::$current_connection_name], $force);
    }

    /**
     * Begin MySQL Transaction
     *
     * @param string|false $connection_name
     *
     * @return DB\Query
     */
    static public function beginTransaction($connection_name = false)
    {
        return self::connect($connection_name)
            ->beginTransaction();
    }

    /**
     * Commit MySQL Transaction
     *
     * @param string|false $connection_name
     *
     * @return DB\Query
     */
    static public function commitTransaction($connection_name = false)
    {
        return self::connect($connection_name)
            ->commitTransaction();
    }

    /**
     * Rollback MySQL Transaction
     *
     * @param string|false $connection_name
     *
     * @return DB\Query
     */
    static public function rollbackTransaction($connection_name = false)
    {
        return self::connect($connection_name)
            ->rollbackTransaction();
    }

    /**
     * Set DB query
     *
     * @param string $query
     *
     * @return DB\Query
     *
     * @throws \InvalidArgumentException
     *          if DB connection is not exists
     **/
    public static function query($query)
    {
        if (!is_string($query) || empty($query)) {
            throw new \InvalidArgumentException('$query must be not empty string');
        }

        return self::connect()
            ->query($query);
    }

    /**
     * Make Update Query
     *
     * @param string $table - table name or JOINed tables, which has the same column names
     *
     * @return DB\Query
     *
     * @throws \InvalidArgumentException
     *            if $table is empty or not string
     **/
    public static function update($table)
    {
        if (!is_string($table) || empty($table)) {
            throw new \InvalidArgumentException('$table must be not empty string');
        }

        return self::connect()
            ->update($table);
    }

    /**
     * Make Insert Query
     *
     * @param string $table - table name or JOINed tables, which has the same column names
     * @param mixed $no_duplication - set DB::DUPLICATE_UPDATE , if needs to update available rows (works through 'ON DUPLICATE KEY')
     *
     * @return DB\Query
     *
     * @throws \InvalidArgumentException
     *            if $table is empty or not string
     **/
    public static function insert($table, $no_duplication = FALSE)
    {
        if (!is_string($table) || empty($table)) {
            throw new \InvalidArgumentException('$table must be not empty string');
        }

        return self::connect()
            ->insert($table, $no_duplication);
    }

    /**
     * Make Repleace Query
     *
     * @param string $table - table name or JOINed tables, which has the same column names
     *
     * @return DB\Query
     *
     * @throws \InvalidArgumentException
     *            if $table is empty or not string
     **/
    public static function replace($table)
    {
        if (!is_string($table) || empty($table)) {
            throw new \InvalidArgumentException('$table must be not empty string');
        }

        return self::connect()
            ->replace($table);
    }


    /**
     * Make Delete Query
     *
     * @param string $table - table name or JOINed tables
     *
     * @return DB\Query
     *
     * @throws \InvalidArgumentException
     *            if $table is empty or not string
     **/
    public static function delete($table)
    {
        if (!is_string($table) || empty($table)) {
            throw new \InvalidArgumentException('$table must be not empty string');
        }

        return self::connect()
            ->delete($table);
    }

    /**
     * Get last insert id from DB
     *
     * @param string $connection_name - connection name
     *
     * @return int
     *
     **/
    public static function lastInsertId($connection_name = FALSE)
    {
        return self::connect($connection_name)
            ->lastInsertId();
    }

    /**
     * Crean all
     *
     * @param string|false $connection_name - name of connection, which needs to destroy
     *             if set FALSE - will destroy all connections
     *             if set NULL - will destroy current connections
     *
     * @return void
     **/
    public static function clean($connection_name = FALSE)
    {
        if (is_null($connection_name)) {
            $connection_name = self::$current_connection_name;
        }

        if (!$connection_name || $connection_name == self::$current_connection_name) {
            self::$current_connection_name = FALSE;
        }

        DB\Query::clean($connection_name);
    }
}
