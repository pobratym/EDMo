<?php

namespace WebXID\EDMo\Rules;

use WebXID\EDMo\AbstractClass\Collection;
use WebXID\EDMo\AbstractClass\CollectionItem;
use Exception;
use WebXID\EDMo\Validation;
use InvalidArgumentException;
use LogicException;

/**
 * Class Field
 *
 * @package WebXID\EDMo\Rules
 *
 * @property string $field_name
 */
class Field extends Collection implements CollectionItem
{
    protected static $readable_properties = [
        'field_name' => true,
    ];

    protected $field_name;
    protected $value_type;
    /** @var Type[] */
    protected $collected_items;
    protected $message = 'Invalid value';

    #region Builders

    /**
     * @param string $field_name
     *
     * @return static
     */
    public static function init(string $field_name = null)
    {
        if ($field_name !== null && empty($field_name)) {
            throw new InvalidArgumentException('Invalid $field_name');
        }

        $object = new static();

        if ($field_name !== null) {
            $object->field_name = $field_name;
        }

        return $object;
    }

    /**
     * @param string $value_type
     * @param array $conditions
     *
     * @return static
     */
    public static function conditions(string $value_type, array $conditions = [])
    {
        switch ($value_type) {
            case Type::BOOL:
            case Type::FLOAT:
            case Type::INT:
            case Type::STRING:
            case Type::ARRAY:
            case Type::IP_ADDRESS:
            case Type::EMAIL:
            case Type::PHONE:
            case Type::ANY:
                break;

            default:
                throw new InvalidArgumentException('Invalid $rule_type');
        }

        $object = static::init();
        $object->value_type = $value_type;
        $types = [];

        foreach ($conditions as $condition) {
            /** @var Type $condition */
            if ($condition->type !== Condition::CALLBACK && isset($types[$condition->type])) {
                throw new InvalidArgumentException('There are duplicated conditions');
            }

            $types[$condition->type] = true;
            $object[] = $condition;
        }

        return $object;
    }

    /**
     * @param Type[] $conditions
     *
     * @return static
     */
    public static function int(array $conditions = [])
    {
        return static::conditions(Type::INT, $conditions);
    }

    /**
     * Checks ID
     *
     * @return static
     */
    public static function Id()
    {
        return static::int([
            Type::itRequired(),
            Type::minValue(1),
        ]);
    }

    /**
     * @param Type[] $conditions
     *
     * @return static
     */
    public static function email(array $conditions = [])
    {
        return static::conditions(Type::EMAIL, $conditions);
    }

    /**
     * @param array $conditions
     *
     * @return static
     */
    public static function phone(array $conditions = [])
    {
        return static::conditions(Type::PHONE, $conditions);
    }

    /**
     * @param Type[] $conditions
     *
     * @return static
     */
    public static function float(array $conditions = [])
    {
        return static::conditions(Type::FLOAT, $conditions);
    }

    /**
     * @param Type[] $conditions
     *
     * @return static
     */
    public static function bool(array $conditions = [])
    {
        return static::conditions(Type::BOOL, $conditions);
    }

    /**
     * @param Type[] $conditions
     *
     * @return static
     */
    public static function ipAddress(array $conditions = [])
    {
        return static::conditions(Type::IP_ADDRESS, $conditions);
    }

    /**
     * @param Type[] $conditions
     *
     * @return static
     */
    public static function string(array $conditions = [])
    {
        return static::conditions(Type::STRING, $conditions);
    }

    /**
     * @param Type[] $conditions
     *
     * @return static
     */
    public static function array(array $conditions = [])
    {
        return static::conditions(Type::ARRAY, $conditions);
    }

    /**
     * @param Type[] $conditions
     *
     * @return static
     */
    public static function any(array $conditions = [])
    {
        return static::conditions(Type::ANY, $conditions);
    }

    #endregion

    #region Setters

    /**
     * @param string $message
     *
     * @return $this
     */
    public function message(string $message)
    {
        if (empty($message)) {
            throw new InvalidArgumentException('Invalid $message');
        }

        $this->message = $message;

        return $this;
    }

    /**
     * @param string $field_name
     *
     * @return $this
     */
    public function fieldName(string $field_name)
    {
        if (empty($field_name)) {
            throw new InvalidArgumentException('Invalid $field_name');
        }

        $this->field_name = $field_name;

        return $this;
    }

    #endregion

    #region Is Conditions methods

    /**
     * @inheritDoc
     */
    protected static function isEntityValid($object) : bool
    {
        return $object instanceof Type;
    }

    /**
     * @param $value
     * @param array $data
     * @param Validation $parent_validation
     *
     * @return bool
     */
    public function check($value, array $data, Validation $parent_validation)
    {
        // Check field data type
        switch ($this->value_type) {
            case Type::BOOL:
                $field_validation = $parent_validation->bool($value, $this->field_name, $this->message);
                break;

            case Type::FLOAT:
                $field_validation = $parent_validation->float($value, $this->field_name, $this->message);
                break;

            case Type::INT:
                $field_validation = $parent_validation->int($value, $this->field_name, $this->message);
                break;

            case Type::STRING:
                $field_validation = $parent_validation->string($value, $this->field_name, $this->message);
                break;

            case Type::ANY:
                if (is_array($value)) {
                    $field_validation = $parent_validation->array($value, $this->field_name, $this->message);

                    break;
                }

                if (is_bool($value)) {
                    $field_validation = $parent_validation->bool($value, $this->field_name, $this->message);

                    break;
                }

                if (is_int($value)) {
                    $field_validation = $parent_validation->int($value, $this->field_name, $this->message);

                    break;
                }

                if (is_numeric($value)) {
                    $field_validation = $parent_validation->float($value, $this->field_name, $this->message);

                    break;
                }

                if (is_string($value) || is_null($value)) {
                    $field_validation = $parent_validation->string($value, $this->field_name, $this->message);

                    break;
                }

                throw new LogicException('Not implemented value type for Field\Type::ANY');

            case Type::ARRAY:
                $field_validation = $parent_validation->array($value, $this->field_name, $this->message);
                break;

            case Type::IP_ADDRESS:
                $field_validation = $parent_validation->ipAddress($value, $this->field_name, $this->message);
                break;

            case Type::PHONE:
                $field_validation = $parent_validation->phone($value, $this->field_name, $this->message);
                break;

            case Type::EMAIL:
                $field_validation = $parent_validation->email($value, $this->field_name, $this->message);
                break;

            default:
                throw new InvalidArgumentException('Invalid value_type');
        }

        // Check field conditions
        foreach ($this->collected_items ?? [] as $index => $condition) {
            switch ($condition->type) {
                // Mandatory conditions

                case Condition::IT_REQUIRD:
                    $field_validation->itRequired($condition->message ?: "`{$this->field_name}` is required");
                    break;

                case Condition::CALLBACK:
                    try {
                        if (
                            !call_user_func_array($condition->value, [$value, $data, $parent_validation])
                            && !$parent_validation->isErrorExist($this->field_name)
                        ) {
                            $parent_validation->addError($this->field_name, $condition->message ?: "`{$this->field_name}` is invalid");
                        }
                    } catch (Exception $e) {
                        $parent_validation->addError($this->field_name, 'Unknown error, please, try again');
                    }

                    break;


                // Optional conditions

                case Condition::MIN_LEN:
                    if ($value === null) {
                        break;
                    }

                    $field_validation->minLen($condition->value, $condition->message ?: "`{$this->field_name}` is too long");

                    break;

                case Condition::MAX_LEN:
                    if ($value === null) {
                        break;
                    }

                    $field_validation->maxLen($condition->value, $condition->message ?: "`{$this->field_name}` is too short");

                    break;

                case Condition::MIN_VALUE:
                    if ($value === null) {
                        break;
                    }

                    $field_validation->minValue($condition->value, $condition->message ?: "`{$this->field_name}` is too small");

                    break;

                case Condition::MAX_VALUE:
                    if ($value === null) {
                        break;
                    }

                    $field_validation->maxValue($condition->value, $condition->message ?: "`{$this->field_name}` is too big");

                    break;

                case Condition::EQUALS:
                    if ($value === null) {
                        break;
                    }

                    $field_validation->equals($condition->value, $condition->message ?: "`{$this->field_name}` has not expected value");
                    break;

                case Condition::NOT_EQUALS:
                    if ($value === null) {
                        break;
                    }

                    $field_validation->notEquals($condition->value, $condition->message ?: "`{$this->field_name}` has not expected value");
                    break;

                case Condition::REGEXP:
                    if ($value === null) {
                        break;
                    }

                    $field_validation->regexp($condition->value, $condition->message ?: "`{$this->field_name}` has not expected value");
                    break;

                case Condition::IN_ARRAY:
                    if ($value === null) {
                        break;
                    }

                    $field_validation->inArray($condition->value, $condition->message ?: "`{$this->field_name}` has not expected value");
                    break;

                case Condition::NOT_IN_ARRAY:
                    if ($value === null) {
                        break;
                    }

                    $field_validation->notInArray($condition->value, $condition->message ?: "`{$this->field_name}` has not expected value");
                    break;

                case Condition::PHONE:
                    if ($value === null) {
                        break;
                    }

                    $field_validation->phone($value, $condition->message ?: "`{$this->field_name}` is invalid");

                    break;

                case Condition::EMAIL:
                    if ($value === null) {
                        break;
                    }

                    $field_validation->email($value, $condition->message ?: "`{$this->field_name}` is invalid");

                    break;

                case Condition::IP_ADDRESS:
                    if ($value === null) {
                        break;
                    }

                    $field_validation->ipAddress($value, $condition->message ?: "`{$this->field_name}` is invalid");

                    break;

                case Condition::FILTER_VAR:
                    if ($value === null) {
                        break;
                    }

                    if (count($condition->value) == 1) {
                        $is_valid = filter_var($value, $condition->value[0]);
                    } elseif (count($condition->value) == 2) {
                        $is_valid = filter_var($value, $condition->value[0], $condition->value[1]);
                    } else {
                        throw new LogicException('Invalid value for Condition::FILTER_VAR');
                    }

                    if ($is_valid === false) {
                        $parent_validation->addError($this->field_name, $condition->message ?: "`{$this->field_name}` is invalid");
                    }

                    break;

            }
        }

        return $parent_validation->isValid();
    }

    #endregion
}
