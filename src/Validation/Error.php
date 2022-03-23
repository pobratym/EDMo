<?php

namespace WebXID\EDMo\Validation;

/**
 * Class Error
 *
 * @package WebXID\EDMo\Validation
 */
class Error
{
    private $errors = [];

    #region Magic methods

    /**
     * @param array $errors
     */
    private function __construct(array $errors) {}

    #endregion

    #region Builders

    /**
     * @param array $errors
     * [
     *         param_name => error_message,
     *         ...
     * ]
     *
     * @return static
     */
    public static function init(array $errors = [])
    {
        return new static($errors);
    }

    #endregion

    #region Setters

    /**
     * @param string $param_name
     * @param string $error_message
     *
     * @return $this
     */
    public function add(string $param_name, string $error_message)
    {
        $this->errors[$param_name] = $error_message;

        return $this;
    }

    /**
     * @param array $error_message
     *
     * @return $this
     */
    public function import(array $error_message)
    {
        $this->errors = array_merge($error_message, $this->errors);

        return $this;
    }


    #endregion

    #region Getters

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->errors;
    }

    #endregion

    #region Is Condition methods

    /**
     * @return bool
     */
    public function isNotEmpty()
    {
        return !empty($this->errors);
    }

    #endregion
}
