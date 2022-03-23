<?php

namespace WebXID\EDMo\DataProcessor;

use WebXID\EDMo\DB;

/**
 * Class AbstractSearch
 *
 * @package WebXID\EDMo\DataProcessor
 */
class AbstractSearch
{
    /**
     * @var null|DB\Build
     */
    protected $db_build = null;

    /**
     * @param string $class_name
     * @param false|string $connection_name
     *
     * @return static
     */
    public static function init(string $joined_tables, array $joined_columns_list, $connection_name = false)
    {
        $object = new static();
        $object->db_build = DB\Build::select($joined_columns_list, $connection_name)
            ->from($joined_tables);

        return $object;
    }

    /**
     * @param string $column_name
     *
     * @return $this
     */
    public function groupBy(string $column_name)
    {
        $this->db_build->groupBy($column_name);

        return $this;
    }

    /**
     * @param string $column_name
     * @param string $order_type
     *
     * @return $this
     */
    public function orderBy(string $column_name, string $order_type = DB\Build::ORDER_BY_ASC)
    {
        $this->db_build->orderBy($column_name, $order_type);

        return $this;
    }

    /**
     * @param int $limit
     * @param int $page
     *
     * @return $this
     */
    public function limit(int $limit, int $page = 1)
    {
        $this->db_build->limit($limit, $page);

        return $this;
    }

    /**
     * @return array
     */
    public function extract() : array
    {
        return $this->db_build->execute();
    }
}
