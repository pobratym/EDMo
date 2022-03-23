<?php

namespace WebXID\EDMo\Validation;

use WebXID\EDMo\Validation\AbstractClass\AbstractRules;

/**
 * Class ArrayRules
 *
 * @package WebXID\EDMo\Validation
 */
class ArrayRules extends AbstractRules
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
            && !is_array($value)
        ) {
            $this->collectError($message);
        }

        $this->value = $value;
    }
}
