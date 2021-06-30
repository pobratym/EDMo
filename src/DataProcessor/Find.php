<?php

/**
 * @copyright Copyright (c) Pavlo Matsura
 * @link https://github.com/pobratym
 */

namespace Pobratym\EDMo\DataProcessor;

use Pobratym\EDMo\DB;

/**
 * Class Find
 *
 * @package Pobratym\EDMo\DataProcessor
 */
class Find extends AbstractSearch
{
	public function find(array $conditions, string $relation = DB\Build::RELATION_AND)
	{
		$this->db_build->find($conditions, $relation);

		return $this;
	}
}
