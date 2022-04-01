<?php

namespace WebXID\EDMo\Rules;

use WebXID\EDMo\AbstractClass\BasicDataContainer;
use WebXID\EDMo\AbstractClass\CollectionItem;
use WebXID\EDMo\Validation;
use InvalidArgumentException;

/**
 * Class Type
 *
 * @package WebXID\EDMo\Rules
 *
 * @property string $type
 * @property mixed $value
 * @property string $message
 */
class Type extends BasicDataContainer  implements CollectionItem
{
    const BOOL = Validation::DATA_TYPE_BOOL;
    const EMAIL = Validation::DATA_TYPE_EMAIL;
    const PHONE = Validation::DATA_TYPE_PHONE;
    const FLOAT = Validation::DATA_TYPE_FLOAT;
    const INT = Validation::DATA_TYPE_INT;
    const IP_ADDRESS = Validation::DATA_TYPE_IP_ADDRESS;
    const STRING = Validation::DATA_TYPE_STRING;
    const ARRAY = 'array';
    const ANY = 'any';

    protected $type;
    protected $value;
    protected $message;

    #region Conditions

    /**
     * @param string|null $message
     *
     * @return static
     */
    public static function itRequired(string $message = null)
    {
        return static::collectConditionConfig(Condition::IT_REQUIRD, null, $message);
    }

    /**
     * @param int $value
     * @param string|null $message
     *
     * @return static
     */
    public static function minLen(int $value, string $message = null)
    {
        return static::collectConditionConfig(Condition::MIN_LEN, $value, $message);
    }

    /**
     * @param int $value
     * @param string|null $message
     *
     * @return static
     */
    public static function maxLen(int $value, string $message = null)
    {
        return static::collectConditionConfig(Condition::MAX_LEN, $value, $message);
    }

    /**
     * @param int $value
     * @param string|null $message
     *
     * @return static
     */
    public static function minValue(int $value, string $message = null)
    {
        return static::collectConditionConfig(Condition::MIN_VALUE, $value, $message);
    }

    /**
     * @param int $value
     * @param string|null $message
     *
     * @return static
     */
    public static function maxValue(int $value, string $message = null)
    {
        return static::collectConditionConfig(Condition::MAX_VALUE, $value, $message);
    }

    /**
     * @param $value
     * @param string|null $message
     *
     * @return static
     */
    public static function equals($value, string $message = null)
    {
        return static::collectConditionConfig(Condition::EQUALS, $value, $message);
    }

    /**
     * @param $value
     * @param string|null $message
     *
     * @return static
     */
    public static function notEquals($value, string $message = null)
    {
        return static::collectConditionConfig(Condition::NOT_EQUALS, $value, $message);
    }

    /**
     * @param string $pattern
     * @param string|null $message
     *
     * @return static
     */
    public static function regexp(string $pattern, string $message = null)
    {
        return static::collectConditionConfig(Condition::REGEXP, $pattern, $message);
    }

    /**
     * @param array $array
     * @param string|null $message
     *
     * @return static
     */
    public static function inArray(array $array, string $message = null)
    {
        return static::collectConditionConfig(Condition::IN_ARRAY, $array, $message);
    }

    /**
     * @param array $array
     * @param string|null $message
     *
     * @return static
     */
    public static function notInArray(array $array, string $message = null)
    {
        return static::collectConditionConfig(Condition::NOT_IN_ARRAY, null, $message);
    }

    /**
     * @param string|null $message
     *
     * @return static
     */
    public static function phone(string $message = null)
    {
        return static::collectConditionConfig(Condition::PHONE, null, $message);
    }

    /**
     * @param string|null $message
     *
     * @return static
     */
    public static function email(string $message = null)
    {
        return static::collectConditionConfig(Condition::EMAIL, null, $message);
    }

    /**
     * @param string|null $message
     *
     * @return static
     */
    public static function ipAddress(string $message = null)
    {
        return static::collectConditionConfig(Condition::IP_ADDRESS, null, $message);
    }

    /**
     * @param callable $function
     * @param string|null $message
     *
     * @return static
     */
    public static function callback(callable $function, string $message = null)
    {
        return static::collectConditionConfig(Condition::CALLBACK, $function, $message);
    }

    /**
     * @param int|int[] $filter_validate // e.g. filter_var('__some value__', $filter_validate[0], $filter_validate[1])
     * [
     *         0 => filter_var_validate_const // required
     *         1 => filter_option // optional
     * ]
     * @param string|null $message
     *
     * @return static
     */
    public static function filterVar($filter_validate, string $message = null)
    {
        $filter_validate = (array) $filter_validate;

        foreach ($filter_validate as $value) {
            if (!is_integer($value) || empty($value)) {
                throw new InvalidArgumentException('Invalid $filter_const');
            }
        }

        return static::collectConditionConfig(Condition::FILTER_VAR, $filter_validate, $message);
    }

    #endregion

    #region Helpers

    /**
     * @param $type
     * @param $value
     * @param $message
     *
     * @return static
     */
    private static function collectConditionConfig($type, $value, $message)
    {
        return static::make([
            'type' => $type,
            'value' => $value,
            'message' => $message,
        ]);
    }

    #endregion
}
