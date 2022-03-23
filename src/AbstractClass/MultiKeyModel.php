<?php

namespace WebXID\EDMo\AbstractClass;

use WebXID\EDMo\DB;
use WebXID\EDMo\DataProcessor;
use WebXID\EDMo\Rules;
use InvalidArgumentException;
use LogicException;
use WebXID\EDMo\Rules\Field;
use RuntimeException;

/**
 * Class Collection
 *
 * @package WebXID\EDMo\AbstractClass
 */
abstract class MultiKeyModel extends BasicEntity
{
	const TABLE_NAME = ''; // Allows to contain single table only
	const JOINED_TABLES = ''; // Allows to contain single table name and/or joined tables with `ON` and `WHERE` sections

	/** @var false|string */
	protected static $db_connection = false;
	/** @var array */
	protected static $columns = [
		// 'column_name' => 'string|50', // data_type|len
		// 'column_name_2' => ['type' => 'string', 'length' => 512],
		// ...
	];
	/** @var array */
	protected static $joined_columns_list = [
		// 'column_name_1', // in case a property name is the same as a column name
		// 'property_name_2' => 't.property_name_2', // in case a property name is the same as a column name, but the column is joined
		// 'property_name_3' => 't.column_name_3 AS property_name_3', // in case a property name is NOT the same as a column name
		// ...
	];

	#region Builders

	/**
	 * @param int $limit
	 * @param int $page
	 *
	 * @return array
	 * [
	 *      0 => [
	 *          column_name => value,
	 *          ...
	 *      ],
	 *      ...
	 * ]
	 */
	public static function all(int $limit = 0, int $page = 1) : array
	{
		$request = DataProcessor::init(static::class)
			->all();

		if ($limit > 0) {
			$request->limit($limit, $page);
		}

		$result = $request->extract();

		foreach ($result as &$entity) {
			static::_filterEntityData($entity);
		}

		return $result;
	}

	/**
	 * @param array $conditions
	 * @param int $limit
	 * @param int $page
	 *
	 * @return array
	 * [
	 *      0 => [
	 *          column_name => value,
	 *          ...
	 *      ],
	 *      ...
	 * ]
	 */
	public static function find(array $conditions, int $limit = 100, int $page = 1) : array
	{
		$request = DataProcessor::init(static::class)
			->find($conditions);

		if ($limit > 0) {
			$request->limit($limit, $page);
		}

		$result = $request->extract();

		foreach ($result as &$entity) {
			static::_filterEntityData($entity);
		}

		return $result;
	}

	/**
	 * @param array $conditions
	 *
	 * @return array
	 */
	public static function findOne(array $conditions)
	{
		$data = static::find($conditions, 1);

		if (!$data) {
			return [];
		}

		return $data[0];
	}

	/**
	 * @param string $where
	 * @param array $binds
	 * @param int $limit
	 * @param int $page
	 *
	 * @return array
	 */
	public static function search(string $where, array $binds = [], int $limit = 100, int $page = 1) : array
	{
		$request = DataProcessor::init(static::class)
			->search($where, $binds);

		if ($limit > 0) {
			$request->limit($limit, $page);
		}

		$result = $request->extract();

		foreach ($result as &$entity) {
			static::_filterEntityData($entity);
		}

		return $result;
	}

	#endregion

	#region Setters

	/**
	 * @param array $conditions
	 */
	public static function remove(array $conditions)
	{
		$where = '';
		$binds = [];

		foreach ($conditions as $column => $value) {
			if ($where) {
				$where .= ' AND ';
			}

			$placeholder = trim($column, '`');

			$where .= " {$column} = :{$placeholder} ";
			$binds[':' . $placeholder] = $value;
		}

		DataProcessor::init(static::class)
			->delete($where)
			->binds($binds)
			->execute();
	}

	/**
	 * @return void
	 */
	public function delete()
	{
		throw new LogicException('The method is not implemented');
	}

	/**
	 * @param array $new_entity_data
	 *
	 * @return int - primary key id if exists
	 */
	public static function addNew(array $new_entity_data)
	{
		return DataProcessor::init(static::class)
			->addNew()
			->save($new_entity_data, DB::DUPLICATE_ERROR);
	}

	/**
	 * @param array $new_entity_data
	 *
	 * @return int - primary key id if exists
	 */
	public static function addNewOrUpdate(array $new_entity_data)
	{
		return DataProcessor::init(static::class)
			->addNew()
			->save($new_entity_data, DB::DUPLICATE_UPDATE);
	}

	/**
	 * @param array $new_entity_data
	 *
	 * @return int|0 - primary key id if exists
	 */
	public static function addNewOrIgnore(array $new_entity_data)
	{
		return DataProcessor::init(static::class)
			->addNew()
			->save($new_entity_data, DB::DUPLICATE_IGNORE);
	}

	#endregion

	#region Getters

	/**
	 * It has to return single entity or null, if there are more data
	 *
	 * @param array $conditions
	 *
	 * @return static|null
	 */
	public static function get($conditions)
	{
		if (!is_array($conditions) || empty($conditions)) {
			throw new InvalidArgumentException('Invalid $conditions');
		}

		$data = self::find($conditions, 2);

		if (count($data) != 1) {
			return null;
		}

		$data = $data[0];

		$rules = static::filterRulesData(static::getModelConfig('columns'), static::getRules());

		if (!$rules->isValid($data)) {
			throw new RuntimeException($rules->validation->getFirstErrorField() . ': ' . $rules->validation->getFirstError());
		}

		$object = new static();
		$object->_load($data);

		return $object;
	}

	/**
	 * @return array|static|false // a default db_connection value is FASLE
	 * [
	 * 		'columns' => array,
	 * 		'joined_columns_list' => array,
	 * 		'db_connection' => string|false,
	 * 		'joined_tables' => string,
	 * 		'table_name' => string,
	 * ]
	 */
	public static function getModelConfig(string $key = null)
	{
		static::_checkModelImplementation();

		if ($key !== null && !is_string($key)) {
			throw new InvalidArgumentException('Invalid $key');
		}

		switch ($key) {
			case 'columns':
				return static::$columns;

			case 'joined_columns_list':
				return static::_getJoinedColumnsList();

			case 'db_connection':
				return static::$db_connection;
		}

		return [
			'columns' => static::$columns,
			'joined_columns_list' => static::_getJoinedColumnsList(),
			'db_connection' => static::$db_connection,
			'table_name' => static::TABLE_NAME,
			'joined_tables' => static::JOINED_TABLES,
		];
	}

	/**
	 * Uses for `select` queries
	 *
	 * @return array
	 */
	protected static function _getJoinedColumnsList()
	{
		return static::$joined_columns_list;
	}

	#endregion

	#region Helpers

	protected static function _checkModelImplementation()
	{
		if (static::TABLE_NAME == '' || str_ireplace([',', 'join', ' '], '', static::TABLE_NAME) != static::TABLE_NAME) {
			throw new LogicException('static::TABLE_NAME was not set');
		}

		if (static::JOINED_TABLES == '') {
			throw new LogicException('static::JOINED_TABLES was not set');
		}

		if (!is_scalar(static::$db_connection)) {
			throw new LogicException('static::$db_connection was not set');
		}

		if (!is_array(static::$columns) || empty(static::$columns)) {
			throw new LogicException('static::$columns was not set');
		}

		if (!is_array(static::_getJoinedColumnsList()) || empty(static::_getJoinedColumnsList())) {
			throw new LogicException('static::$joined_columns_list was not set');
		}
	}

	/**
	 * @param array $model_data
	 */
	protected static function _filterEntityData(array &$model_data) {}

	/**
	 * @param array $data
	 * @param Rules $rules
	 *
	 * @return Rules
	 */
	final public static function filterRulesData(array $data, Rules $rules)
	{
		$result = [];

		foreach ($rules as $index => $rule) {
			/** @var Field $rule */
			if (isset($data[$rule->field_name])) {
				$result[$rule->field_name] = $rule;
			}
		}

		return Rules::import($result);
	}

	#endregion
}
