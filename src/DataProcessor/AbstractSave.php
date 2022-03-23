<?php

namespace WebXID\EDMo\DataProcessor;

use WebXID\EDMo\Validation;

/**
 * Class AbstractSaving
 *
 * @package WebXID\EDMo\DataProcessor
 */
abstract class AbstractSave
{
    protected $add_new_collection_db_settings_data = [
        'table_name' => '',
        'writable_properties' => [
            //'column_name' => 'string|50',
        ],
        'connection_name' => false,
        'pk_column_name' => null,
    ];

    /**
     * @param string $table_name
     * @param array $collumns
     * @param array $pk_column_data - Primary key data
     * @param bool $connection_name
     *
     * @return static
     */
    public static function init(string $table_name, array $collumns, $connection_name = false, $pk_column_name = null)
    {
        $object = new static();
        $object->add_new_collection_db_settings_data = [
            'table_name' => $table_name,
            'writable_properties' => $collumns,
            'connection_name' => $connection_name,
            'pk_column_name' => $pk_column_name,
        ];

        return $object;
    }

    #region Magic methods

    public function __set($property_name, $value)
    {
        if (!isset($this->add_new_collection_db_settings_data['writable_properties'][$property_name])) {
            throw new \InvalidArgumentException("Property `{$property_name}` does not exist or is not writable");
        }

        $property_rule = null;
        $validation = $types = [];
        $len = 0;

        if (is_string($this->add_new_collection_db_settings_data['writable_properties'][$property_name])) {
            $rules = explode('|', $this->add_new_collection_db_settings_data['writable_properties'][$property_name]);

            $types[] = $rules[0];
            $len = $rules[1] ?? 0;
        } elseif (is_array($this->add_new_collection_db_settings_data['writable_properties'][$property_name])) {
            if (!empty($this->add_new_collection_db_settings_data['writable_properties'][$property_name]['type'])) {
                $types = (array) $this->add_new_collection_db_settings_data['writable_properties'][$property_name]['type'];
            }

            if (!empty($this->add_new_collection_db_settings_data['writable_properties'][$property_name]['length'])) {
                $len = $this->add_new_collection_db_settings_data['writable_properties'][$property_name]['length'];
            }
        }

        foreach ($types as $index => $type) {
            /** @var Validation[] $validation */
            $validation[$index] = Validation::rules();

            switch ($type) {
                case Validation::DATA_TYPE_NULL:
                    if ($value !== null) {
                        $property_rule = $validation[$index]->int('', 'Invalid property `' . $property_name . '` value');
                    }

                    break;

                case Validation::DATA_TYPE_BOOL:
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                    if (!is_bool($value)) {
                        $property_rule = $validation[$index]->int('', 'Invalid property `' . $property_name . '` value');
                    } else {
                        $value = (int) $value;
                    }

                    break;

                case Validation::DATA_TYPE_INT:
                    $property_rule = $validation[$index]->int($value, 'Invalid property `' . $property_name . '` value');
                    break;

                case Validation::DATA_TYPE_FLOAT:
                    $property_rule = $validation[$index]->float($value, 'Invalid property `' . $property_name . '` value');
                    break;

                case Validation::DATA_TYPE_STRING:
                    $property_rule = $validation[$index]->string($value, 'Invalid property `' . $property_name . '` value');
                    break;

                case Validation::DATA_TYPE_IP_ADDRESS:
                    $property_rule = $validation[$index]->ipAddress($value, 'Invalid property `' . $property_name . '` value');
                    break;

                case Validation::DATA_TYPE_EMAIL:
                    $property_rule = $validation[$index]->email($value, 'Invalid property `' . $property_name . '` value');
                    break;
            }
        }

        if ($property_rule instanceof Validation\StringRules && !empty($len)) {
            $property_rule->maxLen($len, 'Property length is over allowed value');
        }

        $errors = [];
        $error_count = 0;

        foreach ($validation as $valid) {
            if ($valid->isValid()) {
                continue;
            }

            $errors = $valid->getErrors();
            $error_count ++;
        }

        if ($error_count == count($validation)) {
            throw new \InvalidArgumentException(array_shift($errors));
        }

        $this->$property_name = $value;
    }

    #endregion

    #region Helpers

    /**
     * @param array $properties_array
     *
     * @return $this
     */
    protected function _load(array $properties_array)
    {
        if (!is_array($properties_array) || empty($properties_array)) {
            throw new \InvalidArgumentException('Invalid $properties_array');
        }

        foreach ($properties_array as $property_name => $value) {
            if (isset($this->add_new_collection_db_settings_data['writable_properties'][$property_name])) {
                $this->$property_name = $value;
            }
        }

        return $this;
    }

    protected function validateDBData()
    {
        if (empty($this->add_new_collection_db_settings_data['table_name'])) {
            throw new \InvalidArgumentException('static::TABLE_NAME was not set');
        }

        if (empty($this->add_new_collection_db_settings_data['writable_properties'])) {
            throw new \InvalidArgumentException('There is no property, allowed to write');
        }
    }

    #endregion
}