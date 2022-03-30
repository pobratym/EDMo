<?php

namespace WebXID\EDMo\DataProcessor;

use WebXID\EDMo\DB;

/**
 * Class Update
 *
 * @package WebXID\EDMo\DataProcessor
 */
class Delete extends AbstractSave
{
    private $where = null;
    private $binds = [];

    #region Object methods

    /**
     * @param string $where
     *
     * @return $this
     */
    public function where(string $where)
    {
        $this->where = $where;

        return $this;
    }

    /**
     * @param array $binds
     *
     * @return $this
     */
    public function binds(array $binds)
    {
        if (empty($binds)) {
            throw new \InvalidArgumentException('Invalid $binds');
        }

        foreach ($binds as $placeholder => $value) {
            if (!is_array($value) || !empty($value)) {
                continue;
            }

            throw new \InvalidArgumentException("There is no condition for `{$placeholder}`");
        }

        $this->binds = $binds;

        return $this;
    }

    public function execute()
    {
        $this->validateDBData();

        if ($this->where == null) {
            throw new \InvalidArgumentException('WHERE condition is missed');
        }

        $result = DB::connect($this->add_new_collection_db_settings_data['connection_name'])
            ->delete($this->add_new_collection_db_settings_data['table_name'])
            ->where($this->where);

        if (!empty($this->binds)) {
            $result->binds($this->binds);
        }

        $result->execute();
    }

    #endregion
}
