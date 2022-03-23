<?php

namespace WebXID\EDMo\DB;

use WebXID\EDMo\DB;

/**
 * Class Build
 *
 * @package WebXID\EDMo\DB
 */
class Build
{
    // Relation
    const RELATION_AND = 'AND';
    const RELATION_OR = 'OR';

    // Order By
    const ORDER_BY_ASC = ' ASC ';
    const ORDER_BY_DESC = ' DESC ';

    /**
     * @var array
     * [
     *         column_1,
     *         column_2,
     *         ...
     * ]
     */
    private $select_columns = [];

    /**
     * @var array
     * [
     *         ':placeholder' => value
     *         ...
     * ]
     */
    private $binds = [];

    /**
     * @var array
     * [
     *         column_name => sort_metod
     *         ...
     * ]
     */
    private $order_by = [];

    /**
     * @var array
     * [
     *         column_1,
     *         column_2,
     *         ...
     * ]
     */
    private $group_by = [];

    /**
     * @var array
     * [
     *         column_name => value,
     *         ...
     * ]
     */
    private $column_conditions = [];

    /**@var int */
    private $limit = 0;
    /** @var int */
    private $page = 1;
    /** @var string */
    private $where = '';
    /** @var string */
    private $having = '';
    /** @var string */
    private $table_name = '';
    /** @var bool|string */
    private $connection_name = false;
    /** @var string */
    private $relation = '';

    private function __construct() {}

    #region Builders

    /**
     * @param array $columns
     * @param string|false $connection_name
     *
     * @return static
     */
    public static function select(array $columns = [], $connection_name = false)
    {
        $object = new static();
        $object->select_columns = $columns;
        $object->connection_name = $connection_name;

        return $object;
    }

    #endregion

    #region Object methods

    /**
     * @param string $table_name
     *
     * @return $this
     */
    public function from(string $table_name)
    {
        if (empty($table_name)) {
            throw new \InvalidArgumentException('Invalid $table_name');
        }

        $this->table_name = $table_name;

        return $this;
    }

    /**
     * @param array $conditions
     * @param string $default_relation
     *
     * @return $this
     */
    public function find(array $conditions, string $relation = self::RELATION_AND)
    {
        if (empty($conditions)) {
            throw new \InvalidArgumentException('Invalid $conditions');
        }

        if (!is_string($relation) || empty($relation)) {
            throw new \InvalidArgumentException('Invalid $relation');
        }

        switch ($relation) {
            case static::RELATION_AND:
            case static::RELATION_OR:
                break;

            default:
                throw new \InvalidArgumentException('Invalid $relation');
        }

        $this->relation = $relation;
        $this->column_conditions = $conditions;

        return $this;
    }

    /**
     * @param string $where
     * @param array $binds
     *
     * @return $this
     */
    public function where(string $where, array $binds = [])
    {
        if (empty($where)) {
            throw new \InvalidArgumentException('Invalid $where');
        }

        if (!is_array($binds)) {
            throw new \InvalidArgumentException('Invalid $binds');
        }

        $this->where = $where;
        $this->binds += $binds;

        return $this;
    }

    public function binds(array $binds)
    {
        if (empty($binds)) {
            throw new \InvalidArgumentException('Invalid $binds');
        }

        $this->binds += $binds;
    }

    /**
     * @param int $limit
     * @param int $page
     *
     * @return $this
     */
    public function limit(int $limit, int $page = 1)
    {
        if ($limit < 1) {
            throw new \InvalidArgumentException('Invalid $limit');
        }

        if ($page < 1) {
            throw new \InvalidArgumentException('Invalid $page');
        }

        $this->limit = $limit;
        $this->page = $page;

        return $this;
    }

    /**
     * @param string $column_name
     *
     * @return $this
     */
    public function groupBy(string $column_name)
    {
        if (!is_string($column_name) && empty($column_name)) {
            throw new \InvalidArgumentException('Invalid $column_name');
        }

        $this->group_by[$column_name] = true;

        return $this;
    }

    /**
     * @param string $condition
     *
     * @return $this
     */
    public function having(string $condition)
    {
        if (empty($condition)) {
            throw new \InvalidArgumentException('Invalid $condition');
        }

        $this->having = $condition;

        return $this;
    }

    /**
     * @param string $column_name
     * @param string $order_type
     *
     * @return $this
     */
    public function orderBy(string $column_name, string $order_type = self::ORDER_BY_ASC)
    {
        if (empty($column_name)) {
            throw new \InvalidArgumentException('Invalid $column_name');
        }

        if (empty($order_type)) {
            throw new \InvalidArgumentException('Invalid $order_type');
        }

        $this->order_by[$column_name] = $order_type;

        return $this;
    }

    /**
     * @return array
     */
    public function execute()
    {
        //Define columns
        $select = '*';

        if ($this->select_columns) {
            $select = '';

            foreach ($this->select_columns as $column_name) {
                if (empty($column_name)) {
                    throw new \InvalidArgumentException('Invalid $column_name');
                }

                $select .= ($select ? ',' : '') . ' ' . $column_name;
            }
        }

        // Define Group By
        $group_by = '';

        if ($this->group_by) {
            $group_by = ' GROUP BY ';
            $i = 0;

            foreach ($this->group_by as $column_name) {
                $group_by .= ($i !== 0 ? '' : ',') . ' ' . $column_name;

                $i ++;
            }
        }

        // Define Order By
        $order_by = '';

        if ($this->order_by) {
            $order_by = ' ORDER BY ';
            $i = 0;

            foreach ($this->order_by as $column_name => $sort_type) {
                $order_by .= ($i === 0 ? '' : ',') . " {$column_name} {$sort_type} ";

                $i ++;
            }
        }

        // Define Limit
        $limit = '';

        if ($this->limit > 0) {
            if ($this->page === 1) {
                $page_number = '';
            } else {
                $page_number = ($this->page - 1) * $this->limit;
                $page_number = "{$page_number},";
            }

            $limit = " LIMIT {$page_number} {$this->limit} ";
        }

        // Define conditions
        $where = '';

        foreach ($this->column_conditions as $column_condition => $value) {
            $placeholder_name = ':' . str_replace('.', '_', $column_condition);

            while (isset($this->binds[$placeholder_name])) {
                $placeholder_name .= 1;
            }

            $column_condition = " `" . str_replace('.', '`.`', $column_condition) . "` ";

            if (is_array($value)) {
                $where .= ($where ? $this->relation : '') . " {$column_condition} IN ({$placeholder_name}) ";
            } else {
                $where .= ($where ? $this->relation : '') . " {$column_condition} = {$placeholder_name} ";
            }

            $this->binds[$placeholder_name] = $value;
        }

        if ($this->where && $where) {
            $where = "({$this->where}) AND ({$where})";
        } elseif ($this->where) {
            $where = $this->where;
        }

        if ($where) {
            $where = " WHERE {$where} ";
        }

        // Define having
        $having = '';

        if ($this->having) {
            $having = " HAVING {$this->having} ";
        }

        // Define query
        $query = "
            SELECT {$select}
            FROM {$this->table_name}
            {$where}
            {$group_by}
            {$having}
            {$order_by}
            {$limit}
        ";

        $db = DB::connect($this->connection_name)
            ->query($query);

        if (!empty($this->binds)) {
            $db->binds($this->binds);
        }

        return $db->execute()
            ->fetchArray();
    }

    #endregion
}
