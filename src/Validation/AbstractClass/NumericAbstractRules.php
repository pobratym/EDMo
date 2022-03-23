<?php

namespace WebXID\EDMo\Validation\AbstractClass;

/**
 * Class NumericAbstractRules
 *
 * @package WebXID\EDMo\Validation\AbstractClass
 */
abstract class NumericAbstractRules extends StringAbstractRules
{
    #region Object Methods

    /**
     * @param int $min_value
     * @param string $message
     *
     * @return $this
     */
    public function minValue($min_value, $message)
    {
        if (!is_numeric($min_value)) {
            throw new \InvalidArgumentException('Invalid $min_value');
        }

        if (!is_string($message) || empty($message)) {
            throw new \InvalidArgumentException('Invalid $message');
        }

        if ($this->value < $min_value) {
            $this->collectError($message);
        }

        return $this;
    }

    /**
     * @param int $max_value
     * @param string$message
     *
     * @return $this
     */
    public function maxValue($max_value, $message)
    {
        if (!is_numeric($max_value)) {
            throw new \InvalidArgumentException('Invalid $max_value');
        }

        if (!is_string($message) || empty($message)) {
            throw new \InvalidArgumentException('Invalid $message');
        }

        if ($this->value > $max_value) {
            $this->collectError($message);
        }

        return $this;
    }

    #endregion
}