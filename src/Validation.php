<?php

namespace WebXID\EDMo;

use WebXID\EDMo\Validation\AbstractClass\AbstractRules;
use WebXID\EDMo\Validation\ArrayRules;
use WebXID\EDMo\Validation\BoolRules;
use WebXID\EDMo\Validation\FloatRules;
use WebXID\EDMo\Validation\IntegerRules;
use WebXID\EDMo\Validation\StringRules;
use InvalidArgumentException;
use WebXID\EDMo\Validation\Error;

/**
 * Class Validation
 *
 * @package WebXID\EDMo
 */
class Validation
{
    #region Constants

    const DATA_TYPE_BOOL = 'bool';
    const DATA_TYPE_NULL = 'null';
    const DATA_TYPE_INT = 'int';
    const DATA_TYPE_FLOAT = 'float';
    const DATA_TYPE_STRING = 'string';

    const DATA_TYPE_IP_ADDRESS = 'ip_address';
    const DATA_TYPE_EMAIL = 'email';
    const DATA_TYPE_PHONE = 'phone';

    #endregion

    /** @var AbstractRules[] */
    private $instance = [];
    /** @var array */
    private $errors = [];

    private function __construct() {}

    public static function rules()
    {
        return new static();
    }

    #region Object methods

    /**
     * @param $value
     * @param string $message
     * @param string|null $field_name
     *
     * @return StringRules
     */
    public function string($value, string $field_name = null, $message = 'Data type error')
    {
        if (!is_string($message) || empty($message)) {
            throw new InvalidArgumentException('Invalid $message');
        }

        $instance = StringRules::init($value, $message, $field_name);

        $this->instance[] = $instance;

        return $instance;
    }

    /**
     * @param $value
     * @param string|null $field_name
     * @param string $message
     *
     * @return ArrayRules
     */
    public function array($value, string $field_name = null, $message = 'Data type error')
    {
        if (!is_string($message) || empty($message)) {
            throw new InvalidArgumentException('Invalid $message');
        }

        $instance = ArrayRules::init($value, $message, $field_name);

        $this->instance[] = $instance;

        return $instance;
    }

    /**
     * @param $value
     * @param $message
     *
     * @return IntegerRules
     */
    public function int($value, string $field_name = null, $message = 'Data type error')
    {
        if (!is_string($message) || empty($message)) {
            throw new InvalidArgumentException('Invalid $message');
        }

        $instance = IntegerRules::init($value, $message, $field_name);

        $this->instance[] = $instance;

        return $instance;
    }

    /**
     * @param $value
     * @param $message
     *
     * @return FloatRules
     */
    public function float($value, string $field_name = null, $message = 'Data type error')
    {
        if (!is_string($message) || empty($message)) {
            throw new InvalidArgumentException('Invalid $message');
        }

        $instance = FloatRules::init($value, $message, $field_name);

        $this->instance[] = $instance;

        return $instance;
    }

    /**
     * @param $value
     * @param $message
     *
     * @return StringRules
     */
    public function email($value, string $field_name = null, $message = 'Data type error')
    {
        if (!is_string($message) || empty($message)) {
            throw new InvalidArgumentException('Invalid $message');
        }

        $instance = StringRules::init($value, $message, $field_name);

        $this->instance[] = $instance;

        return $instance->email($value, $message);
    }

    /**
     * @param $value
     * @param string|null $field_name
     * @param string $message
     *
     * @return StringRules
     */
    public function ipAddress($value, string $field_name = null, $message = 'Data type error')
    {
        if (!is_string($message) || empty($message)) {
            throw new InvalidArgumentException('Invalid $message');
        }

        $instance = StringRules::init($value, $message, $field_name);

        $this->instance[] = $instance;

        return $instance->ipAddress($value, $message);
    }

    /**
     * @param $value
     * @param string|null $field_name
     * @param string $message
     *
     * @return StringRules
     */
    public function phone($value, string $field_name = null, $message = 'Data type error')
    {
        if (!is_string($message) || empty($message)) {
            throw new InvalidArgumentException('Invalid $message');
        }

        $instance = StringRules::init($value, $message, $field_name);

        $this->instance[] = $instance;

        return $instance->phone($value, $message);
    }

    /**
     * @param $value
     * @param string|null $field_name
     * @param string $message
     *
     * @return BoolRules
     */
    public function bool($value, string $field_name = null, $message = 'Data type error')
    {
        if (!is_string($message) || empty($message)) {
            throw new InvalidArgumentException('Invalid $message');
        }

        $instance = BoolRules::init($value, $message, $field_name);

        $this->instance[] = $instance;

        return $instance;
    }

    #endregion

    #region Setters

    /**
     * @param string $field_name
     * @param string $message
     *
     * @return $this
     */
    public function addError(string $field_name, string $message)
    {
        if (empty($field_name)) {
            throw new InvalidArgumentException('Invalid $field_name');
        }

        if (empty($message)) {
            throw new InvalidArgumentException('Invalid $message');
        }

        $this->errors[$field_name] = $message;

        return $this;
    }

    #endregion

    #region Getters

    /**
     * @return array
     */
    public function getErrors()
    {
        $errors = $this->errors;

        foreach ($this->instance as $instance) {
            $errors = array_merge($errors, $instance->getErrors());
        }

        return $errors;
    }

    /**
     * @return Error
     */
    public function getErrorInstance()
    {
        return Error::init()->import($this->getErrors());
    }

    /**
     * @return string
     */
    public function getFirstError()
    {
        $errors = $this->getErrors();

        return array_shift($errors);
    }

    /**
     * @return int|string|null
     */
    public function getFirstErrorField()
    {
        foreach ($this->getErrors() as $field_name => $value) {
            return $field_name;
        }

        return null;
    }

    #endregion

    #region Is Condition methods

    /**
     * Returns TRUE, if no error was occurred
     *
     * @return bool
     */
    public function isValid()
    {
        return !(bool) $this->getErrors();
    }

    /**
     * @param $field_name
     *
     * @return bool
     */
    public function isErrorExist($field_name) : bool
    {
        return isset($this->errors[$field_name]);
    }

    #endregion
}