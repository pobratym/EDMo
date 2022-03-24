<?php

namespace WebXID\EDMo\DB;

use WebXID\EDMo\Rules;

/**
 * Class Request
 *
 * @property string $relation
 * @property string $operator - default value of condition operator
 * @property array $column_conditions
 * @property array $order
 *
 * @package WebXID\EDMo\DB
 */
class Request extends \WebXID\EDMo\AbstractClass\BasicEntity
{
    // Relation
    const RELATION_AND = ' AND ';
    const RELATION_OR = ' OR ';

    // Colunm Conditions
    const IN = ' IN ';
    const NOT_IN = ' NOT IN ';
    const LIKE = ' LIKE ';
    const NOT_LIKE = ' NOT LIKE ';
    const MORE = ' > ';
    const LESS = ' < ';
    const EQUAL = ' = ';
    const MORE_EQUAL = ' >= ';
    const LESS_EQUAL = ' <= ';
    const IS_NULL = ' IS NULL '; //has special using way
    const IS_NOT_NULL = ' IS NOT NULL '; //has special using way

    // Order By
    const ORDER_BY_ASC = ' ASC ';
    const ORDER_BY_DESC = ' DESC ';

    protected $relation = self::RELATION_AND;
    protected $operator = self::IN;
    protected $column_conditions = [];
    protected $order = [];

    //Preparing result
    protected $binds = [];
    protected $where = '';
    protected $order_by = '';
    protected $limit = '';

    protected static $readable_properties = [
        'relation' => true,
        'operator' => true,
        'order' => true,
        'column_conditions' => true,
    ];

    protected static $writable_properties = [
        'relation' => true,
        'operator' => true,
        'column_conditions' => true,
        'order' => true,
    ];

    #region Magic methods

    public function __set($property_name, $value)
    {
        if (isset(self::$writable_properties[$property_name])) {
            switch ($property_name) {
                case 'relation':
                    if (!self::isValidRelation($value)) {
                        throw new \InvalidArgumentException('Invalid value of `' . $property_name . '`');
                    }

                    break;

                case 'operator':
                    if (!self::isValidOperator($value)) {
                        throw new \InvalidArgumentException('Invalid value of `' . $property_name . '`');
                    }

                    break;

                case 'column_conditions':
                    if (!is_array($value)) {
                        throw new \InvalidArgumentException('Invalid value of `' . $property_name . '`. It has to be array');
                    }

                    foreach ($value as $val) {
                        if (is_array($val)) {
                            foreach ($val as $key => $v) {
                                if ($v === Request::IS_NULL || $v === Request::IS_NULL) {
                                    continue;
                                }

                                switch ($key) {
                                    case static::IN:
                                    case static::NOT_IN:
                                    case static::LIKE:
                                    case static::NOT_LIKE:
                                    case static::MORE:
                                    case static::LESS:
                                    case static::EQUAL:
                                    case static::MORE_EQUAL:
                                    case static::LESS_EQUAL:
                                        break;

                                    default:
                                        throw new \InvalidArgumentException('Invalid column condition given, `' . print_r($key, true) . '`');
                                }
                            }
                        } elseif (!is_scalar($val) && !is_bool($val)) {
                            throw new \InvalidArgumentException('Invalid column value given, `' . print_r($val, true) . '`');
                        }
                    }

                    break;
            }

            $this->$property_name = $value;

            return;
        }

        throw new \InvalidArgumentException("Property `{$property_name}` does not exist");
    }

    public function __construct() {}

    #endregion

    #region Builders

    public static function init()
    {
        return new self();
    }

    #endregion

    public function execute()
    {
        $i = 0;
        $this->order_by = '';
        $order_by
            = $this->where
            = $this->binds
            = [];

        foreach ($this->column_conditions as $col_name => $conditions) {
            $where = [];
            $bind_col_name = str_replace('.', '_', $col_name);
            $conditions = (array) $conditions;

            foreach ($conditions as $condition_operator => $col_value) {
                if ($col_value === static::IS_NULL || $col_value === static::IS_NOT_NULL) {
                    $where[] = " {$col_name} {$col_value} ";

                    continue;
                }

                //Define request operator
                if (!self::isValidOperator($condition_operator)) {
                    $condition_operator = $this->operator;
                }

                //Define current $i for binds
                if (!isset($this->binds[":{$bind_col_name}{$i}"]) || $this->binds[":{$bind_col_name}{$i}"] != $col_value) {
                    $i ++;
                }

                //Define request placeholder
                $placeholder = ":{$bind_col_name}{$i}";

                switch ($condition_operator) {
                    case static::IN:
                    case static::NOT_IN:
                        $placeholder = "({$placeholder})";

                        break;
                }

                //Collect condition
                $where[] = " {$col_name} {$condition_operator} {$placeholder} ";

                //Collect binds
                if (!isset($this->binds[":{$bind_col_name}{$i}"])) {
                    $this->binds[":{$bind_col_name}{$i}"] = $col_value;
                }
            }

            //Collect condition
            if ($where) {
                $this->where[] = '(' . implode(static::RELATION_OR, $where) . ')';
            }
        }

        //Collect condition
        if ($this->where) {
            $this->where = 'WHERE ' . implode($this->relation, $this->where);
        } else {
            $this->where = '';
        }

        foreach ($this->order as $com_name => $order_type) {
            if (!self::isValidOrderType($order_type)) {
                throw new \InvalidArgumentException('Invalid request order_type: `' . print_r($order_type, true). '`');
            }

            $order_by[] = "{$com_name} {$order_type}";
        }

        if ($order_by) {
            $this->order_by = 'ORDER BY ' . implode(', ', $order_by);
        }
    }

    #region Is Condition methods

    /**
     * Checks request relation
     *
     * @param string $relation
     * @return bool
     */
    private static function isValidRelation($relation)
    {
        if (
            static::RELATION_AND === $relation
            || static::RELATION_OR === $relation
        ) {
            return true;
        }

        return false;
    }

    /**
     * Checks request operator
     *
     * @param string $operator
     * @return bool
     */
    private static function isValidOperator($operator)
    {
        if (
            static::IN === $operator
            || static::NOT_IN === $operator
            || static::LIKE === $operator
            || static::NOT_LIKE === $operator
            || static::MORE === $operator
            || static::LESS === $operator
            || static::EQUAL === $operator
            || static::MORE_EQUAL === $operator
            || static::LESS_EQUAL === $operator
            || static::IS_NULL === $operator
            || static::IS_NOT_NULL === $operator
        ) {
            return true;
        }

        return false;
    }

    /**
     * Checks request order type
     *
     * @param string $order_type
     * @return bool
     */
    private static function isValidOrderType($order_type)
    {
        if (
            static::ORDER_BY_ASC === $order_type
            || static::ORDER_BY_DESC === $order_type
        ) {
            return true;
        }

        return false;
    }

    #endregion

    #region Getters

    /**
     * Returns `Limit` part of MySQL query
     *
     * @return string
     */
    public static function getLimitString($rows_per_page, $page_number = 1)
    {
        $rows_per_page = (int) $rows_per_page;
        $page_number = (int) $page_number;

        if ($rows_per_page < 1) {
            throw new \InvalidArgumentException('Invalid $rows_per_page');
        }

        if ($page_number < 1) {
            throw new \InvalidArgumentException('Invalid $page_number');
        }

        if ($page_number === 1) {
            $page_number = '';
        } else {
            $page_number = ($page_number - 1) * $rows_per_page;
            $page_number = "{$page_number}, ";
        }

        return " LIMIT {$page_number}{$rows_per_page} ";
    }

    /**
     * Returns `limit` part of MySQL query
     *
     * @return string
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Returns `Where` part of MySQL query
     *
     * @return string
     */
    public function getWhere()
    {
        if (empty($this->where)) {
            throw new \BadMethodCallException('Method DB\Request::getWhere() has to be call after DB\Request::execute()');
        }

        return $this->where;
    }

    /**
     * Returns `binds` values of MySQL query
     *
     * @return array
     */
    public function getBinds()
    {
        return $this->binds;
    }

    /**
     * Returns `Order By` part of MySQL query
     *
     * @return string
     */
    public function getOrderBy()
    {
        return $this->order_by;
    }

    /**
     * @return string
     */
    public function getRequestHash()
    {
        return json_encode([
            $this->relation,
            $this->operator,
            $this->column_conditions,
            $this->order,
        ]);
    }

    #endregion

    #region Setters
    /**
     * @param $rows_per_page
     * @param int $page_number
     */
    public function setLimit($rows_per_page, $page_number = 1)
    {
        $rows_per_page = (int) $rows_per_page;
        $page_number = (int) $page_number;

        if ($rows_per_page < 1) {
            throw new \InvalidArgumentException('Invalid $rows_per_page');
        }

        if ($page_number < 1) {
            throw new \InvalidArgumentException('Invalid $page_number');
        }

        $this->limit = static::getLimitString($rows_per_page, $page_number);
    }

    /**
     * @return Rules
     */
    public static function getRules() : Rules
    {
        return Rules::make([]);
    }

    #endregion
}
