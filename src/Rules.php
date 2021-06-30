<?php

/**
 * @copyright Copyright (c) Pavlo Matsura
 * @link https://github.com/pobratym
 */

namespace Pobratym\EDMo;

use Pobratym\EDMo\Rules\Field;
use InvalidArgumentException;
use Pobratym\EDMo\AbstractClass\Collection;

/**
 * Class Rules
 *
 * @package Core
 *
 * @property-read Validation $validation
 */
class Rules extends Collection
{
	protected static $readable_properties = [
		'validation' => true,
	];

	/** @var Validation */
	protected $validation;

	#region Extended

	/**
	 * @inheritDoc
	 */
	protected static function isEntityValid($object) : bool
	{
		return $object instanceof Field;
	}

	#endregion

	#region Magic methods

	/**
	 * @param $offset
	 * @param Field $object
	 */
	public function offsetSet($offset, $object)
	{
		if (!$offset) {
			throw new InvalidArgumentException('Invalid $offset');
		}

		$object->fieldName($offset);

		parent::offsetSet($offset, $object);
	}

	#endregion

	#region Builders

	/**
	 * @param array $fields_rules
	 *
	 * @return static
	 */
	public static function import(array $fields_rules)
	{
		$object = new static();

		foreach ($fields_rules as $field_name => $field_rules) {
			if (!static::isEntityValid($field_rules)) {
				throw new InvalidArgumentException('Invalid $field_rules');
			}

			/** @var Field $field_rules */
			$object[$field_name] = $field_rules->fieldName($field_name);
		}

		return $object;
	}

	#endregion

	#region Setters

	#endregion

	#region Getters

	#endregion

	#region Is Conditions methods

	public function isValid(array $data)
	{
		$this->validation = Validation::rules();

		if (!$this->count()) { // Todo: remove after refactoring athe Model abstraction
			return true;
		}

		foreach ($this as $field_rules) {
			/** @var Field $field_rules */
			$field_rules->check($data[$field_rules->field_name] ?? null, $data, $this->validation);
		}

		return $this->validation->isValid();
	}

	/**
	 * @param string $field_name
	 *
	 * @return false|int|string
	 */
	public function fieldExists(string $field_name)
	{
		foreach ($this->collected_items as $index =>  $field) {
			/** @var Field $field */
			if ($field->field_name === $field_name) {
				return $index;
			}
		}

		return false;
	}

	#endregion
}
