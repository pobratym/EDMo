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
     * @var static[]
     */
    protected static $instance = [];
    /**
     * Method static::toArray() uses this list
     *
     * @var array
     */
    protected static $readable_properties = [];
    protected static $writable_properties = [];

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
     *
     * @return mixed
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

            return $this->{$property_setting}($value);
        }

        return $this->_setProperty($property_name, $value);
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
    public static function create(array $data)
    {
        $object = new static();

        foreach ($data as $property_name => $datum) {
            if (
                !$object->_isReadableProperty($property_name)
                && !$object->_isWritableProperty($property_name)
            ) {
                throw new InvalidArgumentException('Property `' . $property_name . '` cannot be set by method `' . static::class . '::create();`');
            }
        }

        $object->_load($data);

        return $object;
    }

    #endregion

    #region Is Condition methods

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
     *
     * @return $this
     */
    protected function _load(array $properties_array)
    {
        if (!is_array($properties_array)) {
            throw new InvalidArgumentException('Invalid $properties_array');
        }

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
            $this->$property_name = null;
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
            if (static::_isReadableProperty($name)) {
                $result[$name] = $this->$name;
            }
        }

        return $result;
    }

    /**
     * @return Rules
     */
    abstract public static function getRules() : Rules;

    #endregion
}
