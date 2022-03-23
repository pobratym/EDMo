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
    protected static $callable_methods = [];

    protected $_data = [];

    #region Magic Methods

    public function __get($name) {
        if (!isset($this->_data[$name])) {
            return null;
        }

        return $this->_data[$name];
    }

    public function __set($name, $value) {
        return $this->_data[$name] = $value;
    }

    public function __isset($name) {
        return true;
    }

    public function __unset($name)
    {
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
    public static function create(array $data) {
        $object = new static();
        $object->_data = $data;

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

    #endregion
}