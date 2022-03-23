<?php

namespace WebXID\EDMo\Validation\AbstractClass;

/**
 * Class StringAbstractRules
 *
 * @package WebXID\EDMo\Validation\AbstractClass
 */
abstract class StringAbstractRules extends AbstractRules
{
    #region Object Methods

    /**
     * @param string $pattern
     * @param string $message
     *
     * @return $this
     */
    public function regexp($pattern, $message)
    {
        if (!is_string($pattern) || empty($pattern)) {
            throw new \InvalidArgumentException('Invalid $pattern');
        }

        if (!is_string($message) || empty($message)) {
            throw new \InvalidArgumentException('Invalid $message');
        }

        if (!preg_match($pattern, $this->value)) {
            $this->collectError($message);
        }

        return $this;
    }

    /**
     * @param array $required_values
     * @param string $message
     *
     * @return $this
     */
    public function inArray($required_values, $message)
    {
        if (!is_array($required_values) || empty($required_values)) {
            throw new \InvalidArgumentException('Invalid $required_values');
        }

        if (!is_string($message) || empty($message)) {
            throw new \InvalidArgumentException('Invalid $message');
        }

        if (!in_array($this->value, $required_values)) {
            $this->collectError($message);
        }

        return $this;
    }

    /**
     * @param array $required_values
     * @param string $message
     *
     * @return $this
     */
    public function notInArray($required_values, $message)
    {
        if (!is_array($required_values) || empty($required_values)) {
            throw new \InvalidArgumentException('Invalid $required_values');
        }

        if (!is_string($message) || empty($message)) {
            throw new \InvalidArgumentException('Invalid $message');
        }

        if (in_array($this->value, $required_values)) {
            $this->collectError($message);
        }

        return $this;
    }

    #endregion
}