<?php

use WebXID\EDMo\DB;

/**
 * Class DBTest
 */
class DBTest extends AbstractTst
{
    private $default_config = [
        DB::DEFAULT_CONNECTION_NAME => [
            'host' => 'localhost',
            'port' => '3306',
            'user' => 'root',
            'pass' => '',
            'db_name' => 'db_name',
            'use_persistent_connection' => false,
        ],
    ];

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        DB::cleanConfig();

        DB::addConfig($this->default_config);
    }

    #region DB::cleanConfig();

    /**
     * @dataProvider dataProviderTestCleanConfig()
     */
    public function testCleanConfig($connection_name)
    {
        DB::cleanConfig($connection_name);

        $connections_config = $this->getStaticProperty(DB::class, 'connections_config');

        $this->assertTrue([] === $connections_config);
    }

    public function dataProviderTestCleanConfig()
    {
        return [
            ['default'],
            [null],
        ];
    }

    /**
     * @dataProvider dataProvidertestCleanConfigWrong()
     */
    public function testCleanConfigWrong($connection_name)
    {
        $this->expectException(InvalidArgumentException::class);

        DB::cleanConfig($connection_name);
    }

    public function dataProvidertestCleanConfigWrong()
    {
        return [
            [false],
            [true],
            [[]],
            [(object) []],
            [111],
        ];
    }

    #endregion

    #region Add Config

    /**
     *
     */
    public function testAddConfig()
    {
        DB::cleanConfig();
        DB::addConfig($this->default_config);

        $connections_config = $this->getStaticProperty(DB::class, 'connections_config');

        $this->assertSame($this->default_config, $connections_config);
    }

    /**
     * @dataProvider dataProviderTestAddConfigWrong()
     */
    public function testAddConfigWrong($connection_config)
    {
        $this->expectException(InvalidArgumentException::class);

        DB::addConfig($connection_config);
    }

    /**
     * @return array
     */
    public function dataProviderTestAddConfigWrong()
    {
        return [
            [
                false => [
                    'host' => false,
                    'port' => false,
                    'user' => false,
                    'pass' => false,
                    'db_name' => false,
                    'use_persistent_connection' => null,
                    'charset' => false,
                ],
            ],
            [[]],
        ];
    }

    #endregion

    #region Connection

    /**
     *
     */
    public function testConnection()
    {
        $this->assertInstanceOf(DB\Query::class, DB::connect());
    }

    /**
     *
     */
    public function testConnectionWrong2()
    {
        $this->expectException(InvalidArgumentException::class);

        DB::connect('custon_connection');
    }

    #endregion

    #region DB::getLastConnectionName()

    /**
     *
     */
    public function testGetLastConnectionName2()
    {
        $custom_connection = 'custom_connection';

        DB::addConfig(['custom_connection' => $this->default_config[DB::DEFAULT_CONNECTION_NAME]]);

        DB::connect($custom_connection);

        $this->assertSame($custom_connection, DB::getLastConnectionName());
    }

    /**
     * @dataProvider dataProvidertestCleanConfigWrong()
     */
    public function testGetLastConnectionNameWrong($connection_name)
    {
        $this->expectException(LogicException::class);

        $this->setStaticProperty(DB::class, 'current_connection_name', $connection_name);

        DB::getLastConnectionName();
    }

    #endregion

    #region Transactions()

    /**
     * @dataProvider dataProviderTestTransaction()
     */
    public function testTransaction($method_name, $params)
    {
        $query = $this->createMock(DB\Query::class);
        $query->method($method_name)
            ->willReturn($query);

        $this->setStaticProperty(DB\Query::class, 'connections', [DB::DEFAULT_CONNECTION_NAME => $query]);

        if ($params) {
            $result = call_user_func([DB::class, $method_name], $params);
        } else {
            $result = call_user_func([DB::class, $method_name]);
        }

        $this->assertInstanceOf(DB\Query::class, $result);
    }

    public function dataProviderTestTransaction()
    {
        return [
            ['beginTransaction', null],
            ['commitTransaction', null],
            ['rollbackTransaction', null],
            ['query', 'query'],
            ['update', 'table_name'],
            ['insert', 'table_name'],
            ['replace', 'table_name'],
            ['delete', 'table_name'],
            ['lastInsertId', null],

        ];
    }


    #endregion
}
