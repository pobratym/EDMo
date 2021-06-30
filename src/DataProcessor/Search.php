<?php

/**
 * @copyright Copyright (c) Pavlo Matsura
 * @link https://github.com/pobratym
 */

namespace Pobratym\EDMo\DataProcessor;

/**
 * Class Search
 *
 * @package Pobratym\EDMo\DataProcessor
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
