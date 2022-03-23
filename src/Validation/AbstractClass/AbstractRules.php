<?php

namespace WebXID\EDMo\Validation\AbstractClass;

/**
 * Class AbstractRules
 *
 * @package WebXID\EDMo\Validation\AbstractClass
 */
abstract class AbstractRules
{
    /** @var string */
    protected $field_name;
    protected $value;
    /** @var array */
    protected $errors = [];

    /**
     * Rules constructor.
     *
     * @param $value
     * @param $message
     * @param $field_name
     */
    abstract protected function __construct($value, $message, $field_name);

    /**
     * @param $value
     * @param $message
     *
     * @return static
     */
    public static function init($value, $message, string $field_name = null)
    {
        if (!is_string($message) || empty($message)) {
            throw new \InvalidArgumentException('Invalid $message');
        }

        $object = new static($value, $message, $field_name);

        return $object;
    }

    #region Object Methods

    /**
     * Checks, is value empty
     *
     * @param string $message
     *
     * @return $this
     */
    public function itRequired($message)
    {
        if (!is_string($message) || empty($message)) {
            throw new \InvalidArgumentException('Invalid $message');
        }

        if (empty($this->value)) {
            $this->collectError($message);
        }

        return $this;
    }

    /**
     * @param int $min_len
     * @param string $message
     *
     * @return $this
     */
    public function minLen($min_len, $message)
    {
        if (!is_string($message) || empty($message)) {
            throw new \InvalidArgumentException('Invalid $message');
        }

        $min_len = (int) $min_len;
        $len = mb_strlen($this->value);

        if ($len < $min_len) {
            $this->collectError($message);
        }

        return $this;
    }

    /**
     * @param int $max_len
     * @param string$message
     *
     * @return $this
     */
    public function maxLen($max_len, $message)
    {
        if (!is_string($message) || empty($message)) {
            throw new \InvalidArgumentException('Invalid $message');
        }

        $max_len = (int) $max_len;
        $len = mb_strlen($this->value);

        if ($len > $max_len) {
            $this->collectError($message);
        }

        return $this;
    }

    /**
     * @param mixed $required_value
     * @param string $message
     *
     * @return $this
     */
    public function equals($required_value, $message)
    {
        if (!is_string($message) || empty($message)) {
            throw new \InvalidArgumentException('Invalid $message');
        }

        if ($required_value !== $this->value) {
            $this->collectError($message);
        }

        return $this;
    }

    public function notEquals($required_value, $message)
    {
        if (!is_string($message) || empty($message)) {
            throw new \InvalidArgumentException('Invalid $message');
        }

        if ($required_value === $this->value) {
            $this->collectError($message);
        }

        return $this;
    }

    /**
     * @param callable $callback_function - `function ($value) {return bool;}`
     * @param string $message
     *
     * @return $this
     */
    public function callback($callback_function, $message)
    {
        if (!is_callable($callback_function)) {
            throw new \InvalidArgumentException('Invalid $callback_function');
        }

        if (!is_string($message) || empty($message)) {
            throw new \InvalidArgumentException('Invalid $message');
        }

        try {
            if (!call_user_func_array($callback_function, [$this->value])) {
                $this->collectError($message);
            }
        } catch (\Exception $e) {}

        return $this;
    }

    #endregion

    #region Is Condition methods

    /**
     * @return bool
     */
    public function isValid()
    {
        return empty($this->errors);
    }

    #endregion

    #region Helpers

    /**
     * @param string $error_message
     */
    protected function collectError($error_message)
    {
        if (!is_string($error_message) || empty($error_message)) {
            throw new \InvalidArgumentException('Invalid $error_message');
        }

        if ($this->field_name) {
            $this->errors[$this->field_name] = $error_message;
        } else {
            $this->errors[] = $error_message;
        }
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    #endregion
}