<?php

namespace WebXID\EDMo\DataProcessor;

use WebXID\EDMo\DB;

/**
 * Class Find
 *
 * @package WebXID\EDMo\DataProcessor
 */
class Find extends AbstractSearch
{
    public function find(array $conditions, string $relation = DB\Build::RELATION_AND)
    {
        $this->db_build->find($conditions, $relation);

        return $this;
    }
}
