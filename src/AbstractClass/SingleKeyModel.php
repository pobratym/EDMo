<?php

namespace WebXID\EDMo\AbstractClass;

use WebXID\EDMo\DB;
use WebXID\EDMo\DataProcessor;

/**
 * Class Model
 *
 * @package WebXID\EDMo\AbstractClass
 */
abstract class SingleKeyModel extends MultiKeyModel
{
    /** @var string */
    protected static $pk_column_name = ''; // Primary Key column name

    #region Builders

    /**
     * Has to return model instance
     *
     * @param int|string $primary_key_id
     *
     * @return static|null
     */
    public static function get($primary_key_id)
    {
        if (!is_scalar($primary_key_id)) {
            throw new \InvalidArgumentException('Invalid $primary_key_id');
        }

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

        if (!$this->isNovice()) {
            DataProcessor::init(static::class)
                ->update(static::$pk_column_name . " = :pk_column_name")
                ->binds([':pk_column_name' => $this->$pk_column_name])
                ->save($update_data);
        } elseif ($action_on_duplication == DB::DUPLICATE_UPDATE) {
            $this->_setProperty(static::$pk_column_name, static::addNewOrUpdate($update_data));
        } else {
            $this->_setProperty(static::$pk_column_name, static::addNew($update_data));
        }

        $this->savedAction();

        return $this;
    }

    /**
     * @return void
     */
    public function delete()
    {
        static::_checkModelImplementation();

        $pk_column_name = static::$pk_column_name;

        static::remove([
            $pk_column_name => $this->$pk_column_name,
        ]);

        $this->deletedAction();
    }

    #endregion

    #region Is Condition methods

    /**
     * @inheritDoc
     */
    public function isNovice(): bool
    {
        return parent::isNovice() || !$this->_getProperty(static::$pk_column_name);
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

    /**
     * @inheritDoc
     */
    protected function getUniqueKeyConditions() : array
    {
        return [
            static::$pk_column_name => $this->{static::$pk_column_name},
        ];
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
