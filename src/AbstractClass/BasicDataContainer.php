<?php

namespace WebXID\EDMo\AbstractClass;

use LogicException;

/**
 * Class BasicDataContainer
 *
 * @package WebXID\EDMo\AbstractClass
 */
class BasicDataContainer
{
    /** @var string[]  */
    protected static $callable_methods = [
        // string => string,
        // collingMethod => existingObjectMethod,
    ];

    protected $_data = [];

    #region Magic Methods

    /**
     * @param $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        if (!isset($this->_data[$name]) && !property_exists($this, $name)) {
            return null;
        }

        return property_exists($this, $name)
            ? $this->$name
            : $this->_data[$name];
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;

            return;
        }

        $this->_data[$name] = $value;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return true;
    }

    /**
     * @param $name
     */
    public function __unset($name)
    {
        if (property_exists($this, $name)) {
            unset($this->$name);
        }

        unset($this->_data[$name]);
    }

    /**
     * @param string $method_name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $method_name, $arguments)
    {
        if (!static::_isCallableMethod($method_name)) {
            throw new LogicException('The called method does not exist');
        }

        $callable_methods = static::_getCallableMethods();
        $method_settings = $callable_methods[$method_name];

        if (is_string($method_settings)) {
            if (!static::_isMethodExist($method_settings)) {
                throw new LogicException('The method `' . $method_settings . '()` does not exist');
            }

            return call_user_func_array([$this, $method_settings], ['_method_name' => $method_name] + $arguments);
        }

        if (!static::_isMethodExist($method_name)) {
            throw new LogicException('The method `' . $method_name . '()` does not exist');
        }

        return call_user_func_array([$this, $method_name], $arguments);
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

        foreach ($data as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }

    #endregion

    #region Is Condition methods

    /**
     * @param string $method_name
     *
     * @return bool
     */
    final protected function _isMethodExist(string $method_name) : bool
    {
        if (method_exists($this, $method_name)) {
            return true;
        }

        return static::_isCallableMethod($method_name);
    }

    /**
     * @param string $property_name
     *
     * @return bool
     */
    final protected static function _isCallableMethod(string $method_name) : bool
    {
        $callable_properties = static::_getCallableMethods();

        return (bool) ($callable_properties[$method_name] ?? false);
    }

    #endregion

    #region Getters

    /**
     * @return array
     */
    protected static function _getCallableMethods()
    {
        return static::$callable_methods;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $propertyes_list = get_object_vars($this);

        unset($propertyes_list['_data']);

        $propertyes_list = $propertyes_list + $this->_data;

        $result = [];

        foreach ($propertyes_list as $name => $tmp) {
            $result[$name] = $this->$name;
        }

        return $result;
    }

    #endregion
}