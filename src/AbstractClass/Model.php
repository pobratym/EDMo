<?php

namespace WebXID\EDMo\AbstractClass;

use WebXID\EDMo\DB;
use WebXID\EDMo\DataProcessor;

/**
 * Class Model
 *
 * @package WebXID\EDMo\AbstractClass
 */
abstract class Model extends MultiKeyModel
{
    /** @var string */
    protected static $pk_column_name = ''; // Primary Key column name

    /**
     * @deprecated
     * ToDo: needs to remove $primary_key_id from the method. The action requires refactoring
     *
     * @param $primary_key_id
     */
    protected function __construct($primary_key_id = null)
    {
        parent::__construct();

        if ($primary_key_id === null) {
            return;
        }

        $data = static::find([static::$pk_column_name => $primary_key_id], 1, 1);

        if (empty($data[0])) {
            throw new \RuntimeException(substr(strrchr(static::class, '\\'), 1) . ' was not found');
        }

        $this->_load($data[0]);
    }

    #region Builders

    /**
     * Has to return model instance
     *
     * @param int|string $primary_key_id
     *
     * @return Model|static|null
     */
    public static function get($primary_key_id) {
        return parent::get([static::$pk_column_name => $primary_key_id]);
    }

    #endregion

    #region Object methods

    /**
     * @return $this
     */
    public function save($action_on_duplication = DB::DUPLICATE_ERROR)
    {
        static::_checkModelImplementation();

        $pk_column_name = static::$pk_column_name;

        $update_data = [];

        foreach (static::$columns as $col_name => $col_value) {
            if (static::$pk_column_name == $col_name) {
                continue;
            }

            $update_data[$col_name] = $this->{$col_name};
        }

        if (!$this->is_novice()) {
            DataProcessor::init(static::class)
                ->update(static::$pk_column_name . " = :pk_column_name")
                ->binds([':pk_column_name' => $this->$pk_column_name])
                ->save($update_data);
        } elseif ($action_on_duplication == DB::DUPLICATE_UPDATE) {
            $this->_setProperty(static::$pk_column_name, static::addNewOrUpdate($update_data));
        } else {
            $this->_setProperty(static::$pk_column_name, static::addNew($update_data));
        }

        return $this;
    }

    /**
     * @return void
     */
    public function delete()
    {
        static::_checkModelImplementation();

        $pk_column_name = static::$pk_column_name;

        DataProcessor::init(static::class)
            ->delete("{$pk_column_name} = :pk_column_value")
            ->binds([
                ':pk_column_value' => $this->$pk_column_name,
            ])
            ->execute();
    }

    #endregion

    #region Is Condition methods

    /**
     * @return bool
     */
    public function is_novice(): bool
    {
        return !$this->_getProperty(static::$pk_column_name);
    }

    #endregion

    #region Getters

    /**
     * @return array|static|false // a default db_connection value is FASLE
     * [
     *         'columns' => array,
     *         'db_connection' => string|false,
     *         'pk_column_name' => string,
     *         'joined_tables' => string,
     *         'table_name' => string,
     *         'joined_columns_list' => static::_getJoinedColumnsList(),
     * ]
     */
    final public static function getModelConfig(string $key = null)
    {
        static::_checkModelImplementation();

        if ($key === null) {
            return ['pk_column_name' => static::$pk_column_name] + parent::getModelConfig();
        }

        if ('pk_column_name' === $key) {
            return static::$pk_column_name;
        }

        return parent::getModelConfig($key);
    }

    #endregion

    #region Helpers

    protected static function _checkModelImplementation()
    {
        parent::_checkModelImplementation();

        if (!is_string(static::$pk_column_name) || empty(static::$pk_column_name)) {
            throw new \LogicException('static::$pk_column_name was not set');
        }

        if (!isset(static::$columns[static::$pk_column_name])) {
            static::$columns[static::$pk_column_name] = true;
        }
    }

    #endregion
}
