<?php

namespace WebXID\EDMo\DataProcessor;

use WebXID\EDMo\DB;

/**
 * Class AddNew
 *
 * @package WebXID\EDMo\DataProcessor
 */
class AddNew extends AbstractSave
{

    #region Object methods

    /**
     * @param array $properties_array
     *
     * @return int
     */
    public function save(array $properties_array = [], $action_on_duplication = DB::DUPLICATE_ERROR)
    {
        $this->validateDBData();

        if (!empty($properties_array)) {
            $this->_load($properties_array);
        }

        $db_columns = $this->add_new_collection_db_settings_data['writable_properties'];

        $insert_data = [];

        foreach ($db_columns as $column_name => $validation_rules) {
            if (isset($this->$column_name)) {
                $insert_data[$column_name] = $this->$column_name;
            }
        }

        return DB::connect($this->add_new_collection_db_settings_data['connection_name'])
            ->insert($this->add_new_collection_db_settings_data['table_name'], $action_on_duplication)
            ->values($insert_data)
            ->execute()
            ->lastInsertId();
    }

    #endregion
}
