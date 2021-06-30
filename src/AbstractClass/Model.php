<?php

/**
 * @copyright Copyright (c) Pavlo Matsura
 * @link https://github.com/pobratym
 *
 *
 *   HOW TO USE
 * ==============
 *
 * // Implement entity class `User`
 * class User extends \Pobratym\EDMo\AbstractClass\Model
 * 	{
 * 		// Uses for update, insert and delete DB queries
 * 		const TABLE_NAME = 'user';
 * 			// Uses for `select` queries
 * 		const JOINED_TABLES = 'user u
 * 			LEFT JOIN role r ON r.role_id = u.role_id';
 *
 * 		protected static $pk_column_name = 'user_id';
 *
 * 		// Uses for update and insert queries
 * 		protected static $columns = [
 * 			'first_name' => 'string|50',
 * 			'last_name' => 'string|50',
 * 			'role_id' => 'int|11',
 * 		];
 * 		// Uses for `select` queries
 * 		protected static $joined_columns_list = [
 * 			'u.user_id',
 * 			'u.first_name',
 * 			'u.last_name',
 * 			'r.role_id',
 * 			'r.title AS role_name',
 * 		];
 *
 * 		protected static $db_connection = false;
 *
 * 		// Fill this property to allow an object property on read
 * 		protected static $readable_properties = [
 * 			// 'readable_property_name' => true,
 * 		];
 * 		// Fill this property to allow an object property on write
 * 		protected static $writable_properties = [
 * 			// 'writable_property_name' => true,
 * 		];
 * 	}
 *
 * $user_id = User::addNew([
 * 		'first_name' => 'Tony',
 * 		'group_id' => 1,
 * ]);
 *
 * // To get data of single entity by Primary Key
 * $user = User::get($user_id);
 *
 * $user->first_name = 'Jeck';
 * $user->group_id = 2;
 *
 * $user->save();
 *
 * // To get entity data by class `Entity`
 * $user_list_group_2 = User::find(['group_id' = 2]);
 * $full_user_list = User::all();
 *
 * $user->delete();
 *
 * // To get entity data by class `Entity`
 * $entity = EntityCollection::init(User::class);
 *
 * $entity->find()->extract();
 * $entity->search()->extract();
 * $entity->all()->extract();
 */

namespace Pobratym\EDMo\AbstractClass;

use Pobratym\EDMo\DB;
use Pobratym\EDMo\DataProcessor;

/**
 * Class Model
 *
 * @package Pobratym\EDMo\AbstractClass
 */
abstract class Model extends MultiKeyModel
{
	/** @var string */
	protected static $pk_column_name = ''; // Primary Key column name

	/**
	 * @deprecated
	 * ToDo: needs to remove $primary_key_id from the method. The action requires refactoring
	 *
	 * @param $primary_key_id
	 */
	protected function __construct($primary_key_id = null)
	{
		parent::__construct();

		if ($primary_key_id === null) {
			return;
		}

		$data = static::find([static::$pk_column_name => $primary_key_id], 1, 1);

		if (empty($data[0])) {
			throw new \RuntimeException(substr(strrchr(static::class, '\\'), 1) . ' was not found');
		}

		$this->_load($data[0]);
	}

	#region Builders

	/**
	 * Has to return model instance
	 *
	 * @param int|string $primary_key_id
	 *
	 * @return Model|static|null
	 */
	public static function get($primary_key_id) {
		return parent::get([static::$pk_column_name => $primary_key_id]);
	}

	#endregion

	#region Object methods

	/**
	 * @return $this
	 */
	public function save($action_on_duplication = DB::DUPLICATE_ERROR)
	{
		static::_checkModelImplementation();

		$pk_column_name = static::$pk_column_name;

		$update_data = [];

		foreach (static::$columns as $col_name => $col_value) {
			if (static::$pk_column_name == $col_name) {
				continue;
			}

			$update_data[$col_name] = $this->{$col_name};
		}

		if (!$this->is_novice()) {
			DataProcessor::init(static::class)
				->update(static::$pk_column_name . " = :pk_column_name")
				->binds([':pk_column_name' => $this->$pk_column_name])
				->save($update_data);
		} elseif ($action_on_duplication == DB::DUPLICATE_UPDATE) {
			$this->_setProperty(static::$pk_column_name, static::addNewOrUpdate($update_data));
		} else {
			$this->_setProperty(static::$pk_column_name, static::addNew($update_data));
		}

		return $this;
	}

	/**
	 * @return void
	 */
	public function delete()
	{
		static::_checkModelImplementation();

		$pk_column_name = static::$pk_column_name;

		DataProcessor::init(static::class)
			->delete("{$pk_column_name} = :pk_column_value")
			->binds([
				':pk_column_value' => $this->$pk_column_name,
			])
			->execute();
	}

	#endregion

	#region Is Condition methods

	/**
	 * @return bool
	 */
	public function is_novice(): bool
	{
		return !$this->_getProperty(static::$pk_column_name);
	}

	#endregion

	#region Getters

	/**
	 * @return array|static|false // a default db_connection value is FASLE
	 * [
	 * 		'columns' => array,
	 * 		'db_connection' => string|false,
	 * 		'pk_column_name' => string,
	 * 		'joined_tables' => string,
	 * 		'table_name' => string,
	 * 		'joined_columns_list' => static::_getJoinedColumnsList(),
	 * ]
	 */
	final public static function getModelConfig(string $key = null)
	{
		static::_checkModelImplementation();

		if ($key === null) {
			return ['pk_column_name' => static::$pk_column_name] + parent::getModelConfig();
		}

		if ('pk_column_name' === $key) {
			return static::$pk_column_name;
		}

		return parent::getModelConfig($key);
	}

	#endregion

	#region Helpers

	protected static function _checkModelImplementation()
	{
		parent::_checkModelImplementation();

		if (!is_string(static::$pk_column_name) || empty(static::$pk_column_name)) {
			throw new \LogicException('static::$pk_column_name was not set');
		}

		if (!isset(static::$columns[static::$pk_column_name])) {
			static::$columns[static::$pk_column_name] = true;
		}
	}

	#endregion
}
