<?php

namespace WebXID\EDMo;

use WebXID\EDMo\AbstractClass\MultiKeyModel;
use WebXID\EDMo\AbstractClass\SingleKeyModel;
use InvalidArgumentException;
use LogicException;

/**
 * Class DataProcessor
 *
 * @package WebXID\EDMo
 */
class DataProcessor
{
    protected $columns = [
        //'column_name' => 'string|50',
    ];
    protected $joined_columns_list = [
        //'column_name_1',
        //'column_name_2',
        //...
    ];
    protected $db_connection = '';
    protected $joined_tables = '';
    protected $table_name = '';
    protected $pk_column_name = null;

    /**
     * @var static[]
     */
    protected static $instance = [];

    /**
     * @param string $model_class_name
     *
     * @return static
     */
    public static function init(string $model_class_name)
    {
        if (isset(static::$instance[$model_class_name]) && static::$instance[$model_class_name] instanceof self) {
            return static::$instance[$model_class_name];
        }

        /** @var MultiKeyModel $model_class_name */

        $object = new static();

        $parent = class_parents($model_class_name);

        if (isset($parent[SingleKeyModel::class]) || isset($parent[MultiKeyModel::class])) {
            $model_config = $model_class_name::getModelConfig();
        } else {
            throw new InvalidArgumentException('The passed class does not extend MultiKeyModel::class or Model::class');
        }

        if (!is_array($model_config) || empty($model_config)) {
            throw new LogicException("Class {$model_class_name} was not implement correctly. It has to extends class `WebXID\EDMo\AbstractClass\Collection`");
        }

        $object->columns = $model_config['columns'];
        $object->joined_columns_list = $model_config['joined_columns_list'];
        $object->db_connection = $model_config['db_connection'];
        $object->joined_tables = $model_config['joined_tables'];
        $object->table_name = $model_config['table_name'];
        $object->pk_column_name = $model_config['pk_column_name'] ?? null;

        return self::$instance[$model_class_name] = $object;
    }

    #region Object methods

    /**
     * Returns all DB rows
     *
     * @return DataProcessor\AbstractSearch
     */
    public function all()
    {
        return DataProcessor\AbstractSearch::init($this->joined_tables, $this->joined_columns_list, $this->db_connection);
    }

    /**
     * @param array $conditions
     * @param string $relation
     *
     * @return DataProcessor\Find
     */
    public function find(array $conditions, string $relation = DB\Build::RELATION_AND)
    {
        foreach ($conditions as $column_name => $value) {
            if (is_array($value) && !$value) {
                throw new InvalidArgumentException('A SQL placeholder value can not be empty array');
            }

            if (isset($this->joined_columns_list[$column_name])) {
                $conditions[$this->joined_columns_list[$column_name]] = $value;

                unset($conditions[$column_name]);
            }
        }

        return DataProcessor\Find::init($this->joined_tables, $this->joined_columns_list, $this->db_connection)
            ->find($conditions, $relation);
    }

    /**
     * @param string $where
     * @param array $binds
     *
     * @return DataProcessor\Search
     */
    public function search(string $where, array $binds = [])
    {
        return DataProcessor\Search::init($this->joined_tables, $this->joined_columns_list, $this->db_connection)
            ->search($where, $binds);
    }

    /**
     * @return DataProcessor\AddNew
     */
    public function addNew()
    {
        return DataProcessor\AddNew::init($this->table_name, $this->columns, $this->db_connection);
    }

    /**
     * @param $where
     *
     * @return DataProcessor\Update
     */
    public function update($where)
    {
        return DataProcessor\Update::init($this->table_name, $this->columns, $this->db_connection, $this->pk_column_name)
            ->where($where);
    }

    /**
     * @param string $where
     * @param array $binds
     *
     * @return DataProcessor\Delete
     */
    public function delete(string $where)
    {
        return DataProcessor\Delete::init($this->table_name, $this->columns, $this->db_connection, $this->pk_column_name)
            ->where($where);
    }

    #endregion

    #region Is Condition methods

    #endregion
}
