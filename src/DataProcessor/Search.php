<?php

namespace WebXID\EDMo\DataProcessor;

/**
 * Class Search
 *
 * @package WebXID\EDMo\DataProcessor
 */
class Search extends AbstractSearch
{
    /**
     * @param string $where
     * @param array $binds
     *
     * @return $this
     */
    public function search(string $where, array $binds = [])
    {
        $this->db_build->where($where, $binds);

        return $this;
    }
}
