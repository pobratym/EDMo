<?php

namespace WebXID\EDMo\Validation;

use WebXID\EDMo\Validation\AbstractClass\NumericAbstractRules;

/**
 * Class IntegerRules
 *
 * @package WebXID\EDMo\Validation
 */
class IntegerRules extends NumericAbstractRules
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
            && (
                !is_numeric($value)
                || $value != ($value = (int) $value)
            )
        ) {
            $this->collectError($message);
        }

        $this->value = $value;
    }
}
