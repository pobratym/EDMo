<?php

namespace WebXID\EDMo\DataProcessor;

use WebXID\EDMo\DB;

/**
 * Class Update
 *
 * @package WebXID\EDMo\DataProcessor
 */
class Update extends AbstractSave
{
    private $where = null;
    private $binds = [];

    #region Object methods

    /**
     * @param string $where
     *
     * @return $this
     */
    public function where(string $where)
    {
        $this->where = $where;

        return $this;
    }

    /**
     * @param array $binds
     *
     * @return $this
     */
    public function binds(array $binds)
    {
        if (empty($binds)) {
            throw new \InvalidArgumentException('Invalid $binds');
        }

        $this->binds = $binds;

        return $this;
    }

    /**
     * @param array $properties_array
     */
    public function save(array $properties_array = [])
    {
        $this->validateDBData();

        if ($this->where == null) {
            throw new \InvalidArgumentException('WHERE condition is missed');
        }

//        if (empty($this->add_new_collection_db_settings_data['pk_column_value']) || !is_scalar($this->add_new_collection_db_settings_data['pk_column_value'])) {
//            throw new \InvalidArgumentException('The Primary key cannot be empty');
//        }

        if (!empty($properties_array)) {
            $this->_load($properties_array);
        }

        $db_columns = $this->add_new_collection_db_settings_data['writable_properties'];

//        $pk_column_name = $this->add_new_collection_db_settings_data['pk_column_name'];

//        $where = " {$pk_column_name} = :{$pk_column_name} ";
//        $binds[":{$pk_column_name}"] = $this->add_new_collection_db_settings_data['pk_column_value'];

        $update_query = [];

        foreach ($db_columns as $column_name => $validation_rules) {
            if ((property_exists($this, $column_name) && $this->$column_name !== null) || (isset($validation_rules['type']) && is_array($validation_rules['type']) && in_array('null', $validation_rules['type']))) {
                $update_query[$column_name] = $this->$column_name;
            }
        }

        $result = DB::connect($this->add_new_collection_db_settings_data['connection_name'])
            ->update($this->add_new_collection_db_settings_data['table_name'])
            ->values($update_query)
            ->where($this->where);

        if (!empty($this->binds)) {
            $result->binds($this->binds);
        }

        $result->execute();

//        return $this->add_new_collection_db_settings_data['pk_column_value'];
    }

    #endregion
}
