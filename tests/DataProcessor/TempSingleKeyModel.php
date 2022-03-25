<?php

namespace Test\DataProcessor;

use WebXID\EDMo\AbstractClass\SingleKeyModel;
use WebXID\EDMo\Rules;

/**
 * Class TempModel
 *
 * @package DataProcessor
 *
 * @property int $id
 * @property string $title
 * @property int $parent_id
 */
class TempSingleKeyModel extends SingleKeyModel
{
    const TABLE_NAME = 'ut_test_symfony.TempModel'; // Allows to contain single table only
    const JOINED_TABLES = 'ut_test_symfony.TempModel tm'; // Allows to contain single table name and/or joined tables with `ON` and `WHERE`

    protected static $pk_column_name = 'id';

    /** @var array */
    protected static $columns = [
        'title' => true,
        'parent_id' => true,
    ];

    /** @var array */
    protected static $joined_columns_list = [
        'title',
        'parent_id' => 'tm.parent_id',
    ];

    protected static function _getReadableProperties()
    {
        return [
            'id' => true,
            'title' => true,
            'parent_id' => true,
        ];
    }

    /**
     * @return Rules
     */
    public static function getRules() : Rules
    {
        return Rules::make([
            'title' => Rules\Field::string([
                Rules\Type::itRequired(1),
                Rules\Type::minLen(1),
                Rules\Type::maxLen(50),
            ]),
            'parent_id' => Rules\Field::int([
                Rules\Type::minLen(1),
                Rules\Type::maxLen(10),
            ]),
        ]);
    }
}
