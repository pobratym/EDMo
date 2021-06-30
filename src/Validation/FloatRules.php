<?php

/**
 * @copyright Copyright (c) Pavlo Matsura
 * @link https://github.com/pobratym
 */

namespace Pobratym\EDMo\Validation;

/**
 * Class FloatRules
 *
 * @package Pobratym\EDMo\Validation
 */
class FloatRules extends AbstractClass\NumericAbstractRules
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
			&& (
				!is_numeric($value)
				|| $value != ($value = (float) $value)
			)
		) {
			$this->collectError($message);
		}

		$this->value = $value;
	}
}
