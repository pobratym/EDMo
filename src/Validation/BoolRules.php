<?php

/**
 * @copyright Copyright (c) Pavlo Matsura
 * @link https://github.com/pobratym
 */

namespace Pobratym\EDMo\Validation;

use Pobratym\EDMo\Validation\AbstractClass\AbstractRules;

/**
 * Class BoolRules
 *
 * @package Pobratym\EDMo\Validation
 */
class BoolRules extends AbstractRules
{
	protected function __construct($value, $message, $field_name)
	{
		if (!is_string($message) || empty($message)) {
			throw new \InvalidArgumentException('Invalid $message');
		}

		if (is_string($field_name)) {
			$this->field_name = $field_name;
		}

		if (
			$value !== null
			&& !is_bool($value)
		) {
			$this->collectError($message);
		}

		$this->value = $value;
	}
}
