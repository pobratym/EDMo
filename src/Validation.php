<?php

/**
 * @copyright Copyright (c) Pavlo Matsura
 * @link https://github.com/pobratym
 *
 *
 *   HOW TO USE
 * ==============
 *
 * // Init class instance
 * $data = Validation::rules();
 * $test_value = 'some value';
 *
 *
 * ## Check value with string type
 *
 * // Custom string
 * $data->string($test_value, 'Wrong value type')
 * 		->required('String is required')
 * 		->minLen(mb_strlen($test_value), 'Invalid min len')
 * 		->maxLen(mb_strlen($test_value), 'Invalid max len')
 * 		->regexp('/[a-zA-Z ]/', 'Invalid regexp')
 * 		->equals($test_value, 'Invalid equals')
 * 		->notEquals('Hello world!', 'Invalid not equals')
 * 		->enumValues([$test_value], 'Invalid enum');
 *
 * // Check email value
 * $data->email($test_value, 'Invalid email');
 *
 * // Check IP address
 * $data->ipAddress($test_value, 'Invalid IP Address');
 *
 *
 * ## Check value with numeric type
 *
 * // Check integer value
 * $data->int($test_value, 'Wrong value type')
 * 		->required('Int is required')
 * 		->minLen(mb_strlen($test_value), 'Invalid min len')
 * 		->maxLen(mb_strlen($test_value), 'Invalid max len')
 * 		->minValue(100, 'Invalid min value')
 * 		->maxValue(200, 'Invalid max value')
 * 		->regexp('/[a-zA-Z ]/', 'Invalid regexp')
 * 		->equals($test_value, 'Invalid equals')
 * 		->notEquals('Hello world!', 'Invalid not equals')
 * 		->enumValues([$test_value], 'Invalid enum');
 *
 * // Check float value
 * $data->float($test_value, 'Wrong value type');
 *
 * ## Check is value valid
 * if (!$data->isValid()) {
 * 		print_r($data->getError()); // Print all error messages
 * }
 *
 */

namespace Pobratym\EDMo;

use Pobratym\EDMo\Validation\AbstractClass\AbstractRules;
use Pobratym\EDMo\Validation\ArrayRules;
use Pobratym\EDMo\Validation\BoolRules;
use Pobratym\EDMo\Validation\FloatRules;
use Pobratym\EDMo\Validation\IntegerRules;
use Pobratym\EDMo\Validation\StringRules;
use InvalidArgumentException;
use Pobratym\EDMo\Validation\Error;

/**
 * Class Validation
 *
 * @package Pobratym\EDMo
 */
class Validation
{
	#region Constants

	const DATA_TYPE_BOOL = 'bool';
	const DATA_TYPE_NULL = 'null';
	const DATA_TYPE_INT = 'int';
	const DATA_TYPE_FLOAT = 'float';
	const DATA_TYPE_STRING = 'string';

	const DATA_TYPE_IP_ADDRESS = 'ip_address';
	const DATA_TYPE_EMAIL = 'email';
	const DATA_TYPE_PHONE = 'phone';

	#endregion

	/** @var AbstractRules[] */
	private $instance = [];
	/** @var array */
	private $errors = [];

	private function __construct() {}

	public static function rules()
	{
		return new static();
	}

	#region Object methods

	/**
	 * @param $value
	 * @param string $message
	 * @param string|null $field_name
	 *
	 * @return StringRules
	 */
	public function string($value, string $field_name = null, $message = 'Data type error')
	{
		if (!is_string($message) || empty($message)) {
			throw new InvalidArgumentException('Invalid $message');
		}

		$instance = StringRules::init($value, $message, $field_name);

		$this->instance[] = $instance;

		return $instance;
	}

	/**
	 * @param $value
	 * @param string|null $field_name
	 * @param string $message
	 *
	 * @return ArrayRules
	 */
	public function array($value, string $field_name = null, $message = 'Data type error')
	{
		if (!is_string($message) || empty($message)) {
			throw new InvalidArgumentException('Invalid $message');
		}

		$instance = ArrayRules::init($value, $message, $field_name);

		$this->instance[] = $instance;

		return $instance;
	}

	/**
	 * @param $value
	 * @param $message
	 *
	 * @return IntegerRules
	 */
	public function int($value, string $field_name = null, $message = 'Data type error')
	{
		if (!is_string($message) || empty($message)) {
			throw new InvalidArgumentException('Invalid $message');
		}

		$instance = IntegerRules::init($value, $message, $field_name);

		$this->instance[] = $instance;

		return $instance;
	}

	/**
	 * @param $value
	 * @param $message
	 *
	 * @return FloatRules
	 */
	public function float($value, string $field_name = null, $message = 'Data type error')
	{
		if (!is_string($message) || empty($message)) {
			throw new InvalidArgumentException('Invalid $message');
		}

		$instance = FloatRules::init($value, $message, $field_name);

		$this->instance[] = $instance;

		return $instance;
	}

	/**
	 * @param $value
	 * @param $message
	 *
	 * @return StringRules
	 */
	public function email($value, string $field_name = null, $message = 'Data type error')
	{
		if (!is_string($message) || empty($message)) {
			throw new InvalidArgumentException('Invalid $message');
		}

		$instance = StringRules::init($value, $message, $field_name);

		$this->instance[] = $instance;

		return $instance->email($value, $message);
	}

	/**
	 * @param $value
	 * @param string|null $field_name
	 * @param string $message
	 *
	 * @return StringRules
	 */
	public function ipAddress($value, string $field_name = null, $message = 'Data type error')
	{
		if (!is_string($message) || empty($message)) {
			throw new InvalidArgumentException('Invalid $message');
		}

		$instance = StringRules::init($value, $message, $field_name);

		$this->instance[] = $instance;

		return $instance->ipAddress($value, $message);
	}

	/**
	 * @param $value
	 * @param string|null $field_name
	 * @param string $message
	 *
	 * @return StringRules
	 */
	public function phone($value, string $field_name = null, $message = 'Data type error')
	{
		if (!is_string($message) || empty($message)) {
			throw new InvalidArgumentException('Invalid $message');
		}

		$instance = StringRules::init($value, $message, $field_name);

		$this->instance[] = $instance;

		return $instance->phone($value, $message);
	}

	/**
	 * @param $value
	 * @param string|null $field_name
	 * @param string $message
	 *
	 * @return BoolRules
	 */
	public function bool($value, string $field_name = null, $message = 'Data type error')
	{
		if (!is_string($message) || empty($message)) {
			throw new InvalidArgumentException('Invalid $message');
		}

		$instance = BoolRules::init($value, $message, $field_name);

		$this->instance[] = $instance;

		return $instance;
	}

	#endregion

	#region Setters

	/**
	 * @param string $field_name
	 * @param string $message
	 *
	 * @return $this
	 */
	public function addError(string $field_name, string $message)
	{
		if (empty($field_name)) {
			throw new InvalidArgumentException('Invalid $field_name');
		}

		if (empty($message)) {
			throw new InvalidArgumentException('Invalid $message');
		}

		$this->errors[$field_name] = $message;

		return $this;
	}

	#endregion

	#region Getters

	/**
	 * @return array
	 */
	public function getErrors()
	{
		$errors = $this->errors;

		foreach ($this->instance as $instance) {
			$errors = array_merge($errors, $instance->getErrors());
		}

		return $errors;
	}

	/**
	 * @return Error
	 */
	public function getErrorInstance()
	{
		return Error::init()->import($this->getErrors());
	}

	/**
	 * @return string
	 */
	public function getFirstError()
	{
		$errors = $this->getErrors();

		return array_shift($errors);
	}

	/**
	 * @return int|string|null
	 */
	public function getFirstErrorField()
	{
		foreach ($this->getErrors() as $field_name => $value) {
			return $field_name;
		}

		return null;
	}

	#endregion

	#region Is Condition methods

	/**
	 * Returns TRUE, if no error was occurred
	 *
	 * @return bool
	 */
	public function isValid()
	{
		return !(bool) $this->getErrors();
	}

	/**
	 * @param $field_name
	 *
	 * @return bool
	 */
	public function isErrorExist($field_name) : bool
	{
		return isset($this->errors[$field_name]);
	}

	#endregion
}