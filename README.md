It's powerfull lib to work with DB, based on PDO.
It doesn't have dependency to a framework. So, you can use it for any PHP project - actually, that's why the lib was created.
Also, it support multi-connection logic, in case needs to connect to different DBs in a script. 

# Install

1. Run `composer require webxid/edmo`
2. Set DB congif
```php
$default_config = [
    WebXID\EDMo\DB::DEFAULT_CONNECTION_NAME => [
        'host' => 'localhost',
        'port' => '3306',
        'user' => 'root',
        'pass' => '',
        'db_name' => 'db_name',
        'use_persistent_connection' => false,
	'charset' => 'utf8mb4',
    ],
];

WebXID\EDMo\DB::addConfig($default_config);
```
3. Feel free to use the lib 😉


# HOW TO USE

## DB Request

If needs to connect to custom DB, use method connect('connection_name').
Without connect('connection_name'), it will connect to DB::DEFAULT_CONNECTION_NAME .
It works for all query types (query, update, insert, replace, delete, last_insert_id)

### SELECT Query

```php
$rules = [
    1, 2, 3, 4, 5,
];
$query = '    
    SELECT id, name
    FROM users
    WHERE id = :id
        AND rule IN (:rule)
    LIMIT 10
';

//Connecting to default DB
$users = WebXID\EDMo\DB::query($query)
    ->binds([
        ':id' => $id,
        ':rule' => $rules,
    ])
    ->execute()
    ->fetchArray();

//Connecting to custom DB
$old_users = WebXID\EDMo\DB::connect('archive')
    ->query($query)
    ->binds([
        ':id' => $id,
        ':rule' => $rules,
    ])
    ->execute()
    ->fetchArray();
```

### UPDATE Query

```php
//Connecting to default DB
WebXID\EDMo\DB::update('users')
    ->values([
        'rule' => 6,
    ])
    ->where('rule IN (:rule)')
    ->binds([
        ':rule' => $rules,
    ])
    ->execute();
```

### INSERT Query

```php
//Connecting to default DB
WebXID\EDMo\DB::insert('users')
    ->values([
        'rule' => 6,
    ])
    ->execute()
    ->lastInsertId();

WebXID\EDMo\DB::lastInsertId();

//Get last ID from custom DB
WebXID\EDMo\DB::lastInsertId('archive');

//Insert new and Update duplicated row
WebXID\EDMo\DB::insert('users', WebXID\EDMo\DB::DUPLICATE_UPDATE)
    ->values([
        'rule' => 6,
    ])
    ->execute();
```

### REPLACE Query

```php
//Connecting to default DB
WebXID\EDMo\DB::replace('users')
    ->values([
        'rule' => 6,
    ])
    ->execute();
```

### DELETE Query

```php
//Connecting to default DB
WebXID\EDMo\DB::delete('users')
    ->where('rule IN (:rule)')
    ->binds([
        ':rule' => $rules,
    ])
    ->execute();
```

### CLEAN / destroy connections

```php
//Destroy all connections
WebXID\EDMo\DB::clean();

//Destroy one connection, for example, with connection name 'archive'
WebXID\EDMo\DB::clean('archive');

WebXID\EDMo\DB::connect('archive')
    ->delete('users')
    ->where('rule IN (:rule)')
    ->binds([
        ':rule' => $rules,
    ])
    ->execute()
    ->clean();
```

### SQL Transaction

```php
//Open Transaction
$db = WebXID\EDMo\DB::connect()
    ->beginTransaction();

//Revert all changes, which happens inside Transaction
$db->rollbackTransaction();

//Commit all changes, which happens inside Transaction
$db->commitTransaction();
```

## Query Builder

### To get DB rows with conditions, passed to `where()` method
```php
$rows = WebXID\EDMo\DB\Build::select([
        'column_1',
        'column_2',
    ])
    ->from('table_name')
    ->where(' column_1 = :column_1 ')
    ->binds([':column_1' => 123]) // pass here `where` condition value placeholders
    ->groupBy('column_1')
    ->having('column_2')
    ->orderBy('column_3', 'DESC')
    ->limit(5, 3)
    ->execute();

// This query is equals next string:
'
    SELECT column_1, column_2
    FROM table_name
    WHERE column_1 = :column_1
    GROUP BY column_1
    HAVING column_2
    ORDER BY column_3 DESC
    LIMIT 5, 15
';
```

### To get DB rows with conditions, passed to `find()` method
```php
$rows = WebXID\EDMo\DB\Build::select([
        'column_1',
        'column_2',
    ])
    ->from('table_name')
    ->find(['column_name' => 'column_value']) // You don't have to use method `binds()` - logic will do it automatically
    ->groupBy('column_1')
    ->having('column_2')
    ->orderBy('column_3', 'ASC')
    ->limit(5, 2)
    ->execute();

// This query is equals next string:
'
    SELECT column_1, column_2
    FROM table_name
    WHERE column_name = :column_name
    GROUP BY column_1
    HAVING column_2
    ORDER BY column_3 ASC
    LIMIT 5, 10
';
```

### Using `where()` and `find()` in a query
```php
$rows = WebXID\EDMo\DB\Build::select([
        'colunm_1',
        'column_2',
    ])
    ->from('table_name')
    ->where(' column_3 = 1 ')
    ->find(['column_4' => 123])
    ->execute();

// This query is equals next string:
'
    SELECT column_1, column_2
    FROM table_name
    WHERE (column_3 = 1) AND (column_4 = 123)
';
```


## Query Request Data Container 

This class uses to prepare SQL query data for request
```php
$request = new WebXID\EDMo\DB\Request();

$request->relation = Request::RELATION_AND;
$request->operator = Request::MORE_EQUAL; //this is default value of condition operator
$request->column_conditions = [
    'taxonomy_id' => [
        Request::NOT_IN => 1,
        Request::IN => '2'

        Request::IS_NULL,
        Request::NOT_IS_NULL,
    ],
    'parent_id' => 1, // will use default condition operator: $request->operator
];

$request->order = [
    'parent_id' => Request::ORDER_BY_ASC,
    'type' => Request::ORDER_BY_DESC,
];

$request->execute();

print_r([
    $request->getWhere(),
    $request->getBinds(),
    $request->getOrderBy(),
    $request->getLimit(),
    $request->getRequestHash(),
]);
```



## Models

### Model with single Primary key

```php
use WebXID\EDMo\Rules;

// Implement entity class `User`
class User extends WebXID\EDMo\AbstractClass\SingleKeyModel
{
    // Uses for update, insert and delete DB queries
    const TABLE_NAME = 'user';
    // Uses for `select` queries
    const JOINED_TABLES = 'user u
        LEFT JOIN role r ON r.role_id = u.role_id';

    protected static $pk_column_name = 'user_id';

    // Uses for update and insert queries
    protected static $columns = [
        'first_name' => 'string|50',
        'last_name' => 'string|50',
        'role_id' => 'int|11',
    ];
    // Uses for `select` queries
    protected static $joined_columns_list = [
        'u.user_id',
        'u.first_name',
        'u.last_name',
        'r.role_id',
        'r.title AS role_name',
    ];

    protected static $db_connection = false;

    // Fill this property to allow an object property on read
    protected static $readable_properties = [
        // 'readable_property_name' => true,
    ];
    // Fill this property to allow an object property on write
    protected static $writable_properties = [
        // 'writable_property_name' => true,
    ];
    
    /**
    * @inheritDoc
     */
    public static function getRules(): Rules
    {
        return Rules::make([
            'first_name' => Rules\Field::string([
                Rules\Type::itRequired(1),
                Rules\Type::minLen(1),
                Rules\Type::maxLen(50),
            ]),
            'role_id' => Rules\Field::int([
                Rules\Type::minLen(1),
                Rules\Type::maxLen(10),
            ]),
        ]);
    }
}

$user_id = User::addNew([
    'first_name' => 'Tony',
    'group_id' => 1,
]);

// To get data of single entity by Primary Key
$user = User::get($user_id);

$user->first_name = 'Jeck';
$user->group_id = 2;

$user->save();

// To get entity data by class `Entity`
$user_list_group_2 = User::find(['group_id' = 2]);
$full_user_list = User::all();

$user->delete();

// To get entity data by class `Entity`
$entity = WebXID\EDMo\DataProcessor::init(User::class);

$entity->find()->extract();
$entity->search()->extract();
$entity->all()->extract();

```


### Model with Multiple Primary key

A entity does not have single primary key, it could has multi primary key or no one
```php
use WebXID\EDMo\Rules;

// Implement entity class `Option`
class Option extends WebXID\EDMo\AbstractClass\MultiKeyModel
{
    // Uses for update, insert and delete DB queries
    const TABLE_NAME = 'option';
    // Uses for `select` queries
    const JOINED_TABLES = 'option o
        LEFT JOIN config c ON c.key = o.config_key';

    // Uses for update and insert queries
    protected $columns = [
        'key' => 'string|50',
        'value' => 'string|50',
    ];
    // Uses for `select` queries
    protected $joined_columns_list = [
        'o.key',
        'o.value',
        'u.last_name',
        'c.key AS config_key',
    ];
    protected $db_connection = false;
    // Fill this property to allow an object property on read
    protected static $readable_properties = [
        // 'readable_property_name' => true,
    ];
    // Fill this property to allow an object property on write
    protected static $writable_properties = [
        // 'writable_property_name' => true,
    ];
    
    /**
    * @inheritDoc
    */
    protected function getUniqueKeyConditions(): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
        ];
    }
    
    /**
    * @inheritDoc
     */
    public static function getRules(): Rules
    {
        return Rules::make([
            'key' => Rules\Field::string([
                Rules\Type::itRequired(1),
                Rules\Type::minLen(1),
                Rules\Type::maxLen(50),
            ]),
            'value' => Rules\Field::string(),
        ]);
    }
}

// To get entity list
$limit = 1;
$page = 3;

$users_list = Option::all($limit, $page);

// To add new Entity into DB
$user = Option::addNew();

$user->key = 'some_option';
$user->value = 'Smitt';

$user->save();

// Or you can use bulk data loading to add the data
$user = Option::addNew()
    ->save([
        'key' => 'some_option',
        'value' => 'Smitt',
    ]);

// To get entity collection data by class `Grabber`
$entity_collection = WebXID\EDMo\DataProcessor::init(Option::class);
```


## Entities

The class works only with classes that extend abstract class `\WebXID\EDMo\AbstractClass\Collection`

### To init entity collection instance
```php
// class `User` has to extend class `\WebXID\EDMo\AbstractClass\Collection`
$users = WebXID\EDMo\DataProcessor::init(User::class);
```

### Search entity
```php
// To get data without any condition
	$users->all()
    ->orderBy(string)
    ->groupBy(string)
    ->limit(int, int)
    ->extract();

// To find users by simple conditions
$users->find(['permission' => '10'])
    ->orderBy(string)
    ->groupBy(string)
    ->limit(int, int)
    ->extract(); // return users with `permission` = 10

// To find users by complex condition
$users->search('first_name != :first_name', [':first_name' => 'Sam'])
    ->orderBy(string)
    ->groupBy(string)
    ->limit(int, int)
    ->extract();  // return users with `first_name` != 'Sam'
```

### Delete entity
```php
$users->delete('id = :id', [':id' => 1]); // removes row with id = 1
```

### Add new entity
```php
// To add new entity data - can be used only for entities with single Primary key
$new_user = $users->addNew();

$new_user->first_name = 'John';
$new_user->last_name = 'Smitt';

$last_insert_id = $new_user->save(); // returns last_insert_id if table has autoincrement

// Or you can use bulk data loading to add the data
$last_insert_id = $users->addNew($user_id)
    ->save([
        'name' => 'Mark',
    ]);
```

### Update entity data
```php
$new_user = WebXID\EDMo\DataProcessor::init(User::class)
    ->update('user_id = :id')
    ->binds([':id' => $user_id]);

$new_user->name = 'Mark';

$new_user->save();

// Or you can use bulk data loading to add the data
WebXID\EDMo\DataProcessor::init(User::class)
    ->update('user_id = :id')
    ->binds([':id' => $user_id])
    ->save([
        'name' => 'Mark',
    ]);
```



## Data Validation
```php
// Init class instance
$data = Validation::rules();
$test_value = 'some value';
```

### Check value with string type
```php
// Custom string
$data->string($test_value, 'Wrong value type')
    ->required('String is required')
    ->minLen(mb_strlen($test_value), 'Invalid min len')
    ->maxLen(mb_strlen($test_value), 'Invalid max len')
    ->regexp('/^[a-zA-Z ]+$/', 'Invalid regexp')
    ->equals($test_value, 'Invalid equals')
    ->notEquals('Hello world!', 'Invalid not equals')
    ->enumValues([$test_value], 'Invalid enum');

// Check email value
$data->email($test_value, 'Invalid email');

// Check IP address
$data->ipAddress($test_value, 'Invalid IP Address');
```

### Check value with numeric type
```php
// Check integer value
$data->int($test_value, 'Wrong value type')
    ->required('Int is required')
    ->minLen(mb_strlen($test_value), 'Invalid min len')
    ->maxLen(mb_strlen($test_value), 'Invalid max len')
    ->minValue(100, 'Invalid min value')
    ->maxValue(200, 'Invalid max value')
    ->regexp('/^[a-zA-Z ]+$/', 'Invalid regexp')
    ->equals($test_value, 'Invalid equals')
    ->notEquals('Hello world!', 'Invalid not equals')
    ->enumValues([$test_value], 'Invalid enum');

// Check float value
$data->float($test_value, 'Wrong value type');
```

### Check is value valid
```php
if (!$data->isValid()) {
    print_r($data->getError()); // Print all error messages
}
```
