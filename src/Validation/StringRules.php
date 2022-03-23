<?php

namespace WebXID\EDMo\Validation;

use WebXID\EDMo\Validation\AbstractClass\StringAbstractRules;

/**
 * Class StringRules
 *
 * @package WebXID\EDMo\Validation
 */
class StringRules extends StringAbstractRules
{
    protected function __construct($value, $message, $field_name)
    {
        if (!is_string($message) || empty($message)) {
            throw new \InvalidArgumentException('Invalid $message');
        }

        if (is_string($field_name)) {
            $this->field_name = $field_name;
        }

        if (
            $value !== null
            && !is_string($value)
            && !is_numeric($value)
            && !empty($value)
        ) {
            $this->collectError($message);
        }

        $this->value = $value;
    }

    /**
     * @param $value
     * @param $message
     *
     * @return $this
     */
    public function phone($value, $message)
    {
        if (!is_string($message) || empty($message)) {
            throw new \InvalidArgumentException('Invalid $message');
        }

        $numbers_only = preg_replace("/[^0-9]/", '', $value);
        $number_of_digits = strlen($numbers_only);


        if ($number_of_digits < 10 || $number_of_digits > 12) {
            $this->collectError($message);
        }

        return $this;
    }

    /**
     * @param $value
     * @param $message
     *
     * @return StringRules
     */
    public function email($value, $message)
    {
        if (!is_string($message) || empty($message)) {
            throw new \InvalidArgumentException('Invalid $message');
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->collectError($message);
        }

        return $this;
    }

    /**
     * @param $value
     * @param $message
     *
     * @return StringRules
     */
    public function ipAddress($value, $message)
    {
        if (!is_string($message) || empty($message)) {
            throw new \InvalidArgumentException('Invalid $message');
        }

        if (!filter_var($value, FILTER_VALIDATE_IP)) {
            $this->collectError($message);
        }

        return $this;
    }
}
