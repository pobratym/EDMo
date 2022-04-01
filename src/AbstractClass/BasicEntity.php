<?php

namespace WebXID\EDMo\AbstractClass;

use WebXID\EDMo\Rules;
use InvalidArgumentException;
use LogicException;

/**
 * Class BasicEntity
 *
 * @package WebXID\EDMo\AbstractClass
 */
abstract class BasicEntity extends BasicDataContainer
{
    /**
     * Method static::toArray() uses this list
     *
     * @var array
     */
    protected static $readable_properties = [
        // string => string|true|null,
        // 'property_name' => 'getterMethodName', // returns the method $this->getterMethodName() result
        // 'property_name' => true, // returns $this->property_name or $this->_data['property_name']
    ];
    protected static $writable_properties = [
        // string => string|true|null,
        // 'property_name' => 'setterMethodName', // call the method $this->setterMethodName($passed_value)
        // 'property_name' => true, // sets $this->property_name, if exists or $this->_data['property_name']
    ];

    /** @var bool */
    private $is_novice = true;

    #region Abstract methods

    /**
     * @return Rules
     */
    abstract public static function getRules() : Rules;

    #endregion

    #region Magic Methods

    protected function __construct() {}

    /**
     * @param $property_name
     *
     * @return mixed|null
     */
    public function __get($property_name)
    {
        if (!static::_isReadableProperty($property_name)) {
            throw new InvalidArgumentException("Property `{$property_name}` does not exist or is not readable");
        }

        $readable_properties = static::_getReadableProperties();
        $property_setting = $readable_properties[$property_name];

        if (is_string($property_setting)) {
            if (!static::_isMethodExist($property_setting)) {
                throw new LogicException('Method ' . $property_name . ' does not exist');
            }

            return $this->{$property_setting}();
        }

        return $this->_getProperty($property_name);
    }

    /**
     * @param $property_name
     *
     * @return bool
     */
    public function __isset($property_name)
    {
        return static::_isReadableProperty($property_name);
    }

    /**
     * @param $property_name
     * @param $value
     */
    public function __set($property_name, $value)
    {
        if (!static::_isWritableProperty($property_name)) {
            throw new InvalidArgumentException("Property `{$property_name}` does not exist or is not writable. Class name `" . static::class . "`");
        }

        $writable_properties = static::_getWritableProperties();
        $property_setting = $writable_properties[$property_name];

        if (is_string($property_setting)) {
            if (!static::_isMethodExist($property_setting)) {
                throw new LogicException('Method ' . $property_name . ' does not exist');
            }

            $this->{$property_setting}($value);

            return;
        }

        $this->_setProperty($property_name, $value);
    }

    /**
     * @param $property_name
     *
     * @return mixed|null
     */
    public function __unset($property_name)
    {
        if (!static::_isWritableProperty($property_name)) {
            throw new LogicException('The logic has no affect');
        }

        $this->_unsetProperty($property_name);

        return null;
    }

    #endregion

    #region Builders

    /**
     * @param array $data
     *
     * @return static
     */
    public static function make(array $data = [])
    {
        $object = new static();

        foreach ($data as $property_name => $datum) {
            if (
                !$object->_isReadableProperty($property_name)
                && !$object->_isWritableProperty($property_name)
            ) {
                throw new InvalidArgumentException('Property `' . $property_name . '` cannot be set by method `' . static::class . '::make();`');
            }
        }

        $object->_load($data);

        return $object;
    }

    #endregion

    #region Actions

    /**
     * Calls after a model saved in DB by $this->save() method
     *
     * @return void
     */
    protected function savedAction()
    {
        $this->is_novice = false;
    }

    /**
     * Calls after a model deleted in DB by $this->delete() method
     *
     * @return void
     */
    protected function deletedAction()
    {
        $this->is_novice = true;
    }

    #endregion

    #region Is Condition methods

    /**
     * Returns TRUE if a model does not saved in DB at all
     *
     * @return bool
     */
    public function isNovice(): bool
    {
        return $this->is_novice;
    }

    /**
     * @param string $property_name
     *
     * @return bool
     */
    final protected static function _isReadableProperty(string $property_name) : bool
    {
        $readable_properties = static::_getReadableProperties();

        return (bool) ($readable_properties[$property_name] ?? false);
    }

    /**
     * @param string $property_name
     *
     * @return bool
     */
    final protected static function _isWritableProperty(string $property_name) : bool
    {
        $writable_properties = static::_getWritableProperties();

        return (bool) ($writable_properties[$property_name] ?? false);
    }

    #endregion

    #region Setters

    /**
     * @param array $properties_array
     * @param bool $is_novice
     *
     * @return $this
     */
    protected function _load(array $properties_array, bool $is_novice = true)
    {
        if (!is_array($properties_array)) {
            throw new InvalidArgumentException('Invalid $properties_array');
        }

        $this->is_novice = $is_novice;

        foreach ($properties_array as $property_name => $value) {
            $this->_setProperty($property_name, $value);
        }

        return $this;
    }

    /**
     * Set for usage inside an instance
     *
     * @param string $property_name
     * @param $value
     *
     * @return mixed
     */
    final protected function _setProperty(string $property_name, $value)
    {
        if (property_exists($this, $property_name)) {
            return $this->$property_name = $value;
        }

        return parent::__set($property_name, $value);
    }

    /**
     * Unset for usage inside an instance
     *
     * @param string $property_name
     */
    final protected function _unsetProperty(string $property_name)
    {
        if (!$property_name) {
            throw new InvalidArgumentException('Invalid $property_name');
        }

        if (property_exists($this, $property_name)) {
            unset($this->$property_name);
        }

        parent::__unset($property_name);
    }

    #endregion

    #region Getters

    /**
     * Get for usage inside an instance
     *
     * @param string $property_name
     *
     * @return mixed|null
     */
    final protected function _getProperty(string $property_name)
    {
        if (property_exists($this, $property_name)) {
            return $this->$property_name;
        }

        return parent::__get($property_name);
    }

    /**
     * Fill this property to allow an object property on read
     *
     * @return array
     */
    protected static function _getReadableProperties()
    {
        return static::$readable_properties;
    }

    /**
     * Fill this property to allow an object property on write
     *
     * @return array
     */
    protected static function _getWritableProperties()
    {
        return static::$writable_properties;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [];

        foreach (static::_getReadableProperties() as $name => $settings) {
            $result[$name] = $this->$name;
        }

        return $result;
    }

    #endregion
}
