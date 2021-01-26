<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test\Platform;

use Windwalker\Database\Platform\SQLServerPlatform;
use Windwalker\Test\Helper\TestStringHelper;
use Windwalker\Utilities\Str;

/**
 * The SQLServerPlatformTest class.
 */
class SQLServerPlatformTest extends AbstractPlatformTest
{
    protected static string $platform = 'SQLServer';

    protected static string $driver = 'pdo_sqlsrv';

    protected static string $schema = 'dbo';

    /**
     * @var SQLServerPlatform
     */
    protected $instance;

    /**
     * @see  SQLServerPlatform::listDatabases()
     */
    public function testListDatabases(): void
    {
        $databases = $this->instance->listDatabases();

        self::assertContains(
            self::getTestParams()['database'],
            $databases
        );
    }

    /**
     * @see  SQLServerPlatform::listSchemas
     */
    public function testListSchemas(): void
    {
        $schemas = $this->instance->listSchemas();

        $defaults = [
            'dbo',
            'guest',
            'sys',
        ];

        self::assertEquals(
            $defaults,
            array_values(
                array_intersect(
                    $schemas,
                    $defaults
                )
            )
        );
    }

    /**
     * @see  SQLServerPlatform::getTables
     */
    public function testGetTables(): void
    {
        $tables = $this->instance->listTables(static::$schema);

        self::assertEquals(
            [
                'ww_articles' => [
                    'TABLE_NAME' => 'ww_articles',
                    'TABLE_SCHEMA' => 'dbo',
                    'TABLE_TYPE' => 'BASE TABLE',
                    'VIEW_DEFINITION' => null,
                    'CHECK_OPTION' => null,
                    'IS_UPDATABLE' => null
                ],
                'ww_categories' => [
                    'TABLE_NAME' => 'ww_categories',
                    'TABLE_SCHEMA' => 'dbo',
                    'TABLE_TYPE' => 'BASE TABLE',
                    'VIEW_DEFINITION' => null,
                    'CHECK_OPTION' => null,
                    'IS_UPDATABLE' => null
                ]
            ],
            $tables
        );
    }

    /**
     * @see  SQLServerPlatform::getViews
     */
    public function testGetViews(): void
    {
        $views = $this->instance->listViews(static::$schema);

        $views['ww_articles_view']['VIEW_DEFINITION'] = Str::replaceCRLF($views['ww_articles_view']['VIEW_DEFINITION']);

        self::assertEquals(
            [
                'ww_articles_view' => [
                    'TABLE_NAME' => 'ww_articles_view',
                    'TABLE_SCHEMA' => 'dbo',
                    'TABLE_TYPE' => 'VIEW',
                    'VIEW_DEFINITION' => Str::replaceCRLF('

CREATE VIEW ww_articles_view AS SELECT * FROM ww_articles;'),
                    'CHECK_OPTION' => 'NONE',
                    'IS_UPDATABLE' => 'NO'
                ]
            ],
            $views
        );
    }

    /**
     * @see  SQLServerPlatform::getColumns
     */
    public function testGetColumns(): void
    {
        $columns = $this->instance->listColumns('#__articles', static::$schema);

        self::assertEquals(
            [
                'column_name',
                'ordinal_position',
                'column_default',
                'is_nullable',
                'data_type',
                'character_maximum_length',
                'character_octet_length',
                'numeric_precision',
                'numeric_scale',
                'numeric_unsigned',
                'comment',
                'auto_increment',
                'erratas'
            ],
            array_keys($columns[array_key_first($columns)])
        );

        self::assertEquals(
            [
                'id' => [
                    'column_name' => 'id',
                    'ordinal_position' => 1,
                    'column_default' => null,
                    'is_nullable' => false,
                    'data_type' => 'int',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => 10,
                    'numeric_scale' => 0,
                    'numeric_unsigned' => false,
                    'comment' => '',
                    'auto_increment' => true,
                    'erratas' => [

                    ]
                ],
                'category_id' => [
                    'column_name' => 'category_id',
                    'ordinal_position' => 2,
                    'column_default' => '0',
                    'is_nullable' => true,
                    'data_type' => 'int',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => 10,
                    'numeric_scale' => 0,
                    'numeric_unsigned' => false,
                    'comment' => '',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'page_id' => [
                    'column_name' => 'page_id',
                    'ordinal_position' => 3,
                    'column_default' => '0',
                    'is_nullable' => false,
                    'data_type' => 'int',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => 10,
                    'numeric_scale' => 0,
                    'numeric_unsigned' => false,
                    'comment' => '',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'type' => [
                    'column_name' => 'type',
                    'ordinal_position' => 4,
                    'column_default' => 'bar',
                    'is_nullable' => false,
                    'data_type' => 'char',
                    'character_maximum_length' => 15,
                    'character_octet_length' => 15,
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => '',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'price' => [
                    'column_name' => 'price',
                    'ordinal_position' => 5,
                    'column_default' => '0.0',
                    'is_nullable' => true,
                    'data_type' => 'decimal',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => 20,
                    'numeric_scale' => 6,
                    'numeric_unsigned' => false,
                    'comment' => '',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'title' => [
                    'column_name' => 'title',
                    'ordinal_position' => 6,
                    'column_default' => '',
                    'is_nullable' => false,
                    'data_type' => 'varchar',
                    'character_maximum_length' => 255,
                    'character_octet_length' => 255,
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => '',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'alias' => [
                    'column_name' => 'alias',
                    'ordinal_position' => 7,
                    'column_default' => '',
                    'is_nullable' => false,
                    'data_type' => 'varchar',
                    'character_maximum_length' => 255,
                    'character_octet_length' => 255,
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => '',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'introtext' => [
                    'column_name' => 'introtext',
                    'ordinal_position' => 8,
                    'column_default' => null,
                    'is_nullable' => false,
                    'data_type' => 'varchar',
                    'character_maximum_length' => -1,
                    'character_octet_length' => -1,
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => '',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'state' => [
                    'column_name' => 'state',
                    'ordinal_position' => 9,
                    'column_default' => '0',
                    'is_nullable' => false,
                    'data_type' => 'tinyint',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => 3,
                    'numeric_scale' => 0,
                    'numeric_unsigned' => false,
                    'comment' => '',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'ordering' => [
                    'column_name' => 'ordering',
                    'ordinal_position' => 10,
                    'column_default' => '0',
                    'is_nullable' => false,
                    'data_type' => 'int',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => 10,
                    'numeric_scale' => 0,
                    'numeric_unsigned' => false,
                    'comment' => '',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'created' => [
                    'column_name' => 'created',
                    'ordinal_position' => 11,
                    'column_default' => '1000-01-01 00:00:00',
                    'is_nullable' => false,
                    'data_type' => 'datetime',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => '',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'created_by' => [
                    'column_name' => 'created_by',
                    'ordinal_position' => 12,
                    'column_default' => '0',
                    'is_nullable' => false,
                    'data_type' => 'int',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => 10,
                    'numeric_scale' => 0,
                    'numeric_unsigned' => false,
                    'comment' => '',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'language' => [
                    'column_name' => 'language',
                    'ordinal_position' => 13,
                    'column_default' => '',
                    'is_nullable' => false,
                    'data_type' => 'char',
                    'character_maximum_length' => 7,
                    'character_octet_length' => 7,
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => '',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'params' => [
                    'column_name' => 'params',
                    'ordinal_position' => 14,
                    'column_default' => null,
                    'is_nullable' => false,
                    'data_type' => 'text',
                    'character_maximum_length' => 2147483647,
                    'character_octet_length' => 2147483647,
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => '',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ]
            ],
            $columns
        );
    }

    /**
     * @see  SQLServerPlatform::getConstraints
     */
    public function testGetConstraints(): void
    {
        $constraints = $this->instance->listConstraints('#__articles', static::$schema);

        self::assertEquals(
            [
                'pk_ww_articles' => [
                    'constraint_name' => 'pk_ww_articles',
                    'constraint_type' => 'PRIMARY KEY',
                    'table_name' => 'ww_articles',
                    'columns' => [
                        'id'
                    ]
                ],
                'fk_articles_category_id' => [
                    'constraint_name' => 'fk_articles_category_id',
                    'constraint_type' => 'FOREIGN KEY',
                    'table_name' => 'ww_articles',
                    'columns' => [
                        'category_id'
                    ],
                    'referenced_table_schema' => 'dbo',
                    'referenced_table_name' => 'ww_categories',
                    'referenced_columns' => [
                        'id'
                    ],
                    'match_option' => 'SIMPLE',
                    'update_rule' => 'SET NULL',
                    'delete_rule' => 'SET NULL'
                ],
                'fk_articles_category_more' => [
                    'constraint_name' => 'fk_articles_category_more',
                    'constraint_type' => 'FOREIGN KEY',
                    'table_name' => 'ww_articles',
                    'columns' => [
                        'page_id',
                        'created_by'
                    ],
                    'referenced_table_schema' => 'dbo',
                    'referenced_table_name' => 'ww_categories',
                    'referenced_columns' => [
                        'parent_id',
                        'level'
                    ],
                    'match_option' => 'SIMPLE',
                    'update_rule' => 'NO ACTION',
                    'delete_rule' => 'NO ACTION'
                ]
            ],
            $constraints
        );
    }

    public function testGetIndexes(): void
    {
        $indexes = $this->instance->listIndexes('#__articles', static::$schema);

        self::assertEquals(
            [
                'PK__ww_articles' => [
                    'table_schema' => 'dbo',
                    'table_name' => 'ww_articles',
                    'is_unique' => true,
                    'is_primary' => true,
                    'index_name' => $indexes['PK__ww_articles']['index_name'],
                    'index_comment' => '',
                    'columns' => [
                        'id' => [
                            'column_name' => 'id',
                            'sub_part' => null
                        ]
                    ]
                ],
                'idx_articles_alias' => [
                    'table_schema' => 'dbo',
                    'table_name' => 'ww_articles',
                    'is_unique' => true,
                    'is_primary' => false,
                    'index_name' => 'idx_articles_alias',
                    'index_comment' => '',
                    'columns' => [
                        'type' => [
                            'column_name' => 'type',
                            'sub_part' => null
                        ],
                        'alias' => [
                            'column_name' => 'alias',
                            'sub_part' => null
                        ]
                    ]
                ],
                'idx_articles_category_id' => [
                    'table_schema' => 'dbo',
                    'table_name' => 'ww_articles',
                    'is_unique' => false,
                    'is_primary' => false,
                    'index_name' => 'idx_articles_category_id',
                    'index_comment' => '',
                    'columns' => [
                        'category_id' => [
                            'column_name' => 'category_id',
                            'sub_part' => null
                        ]
                    ]
                ],
                'idx_articles_created_by' => [
                    'table_schema' => 'dbo',
                    'table_name' => 'ww_articles',
                    'is_unique' => false,
                    'is_primary' => false,
                    'index_name' => 'idx_articles_created_by',
                    'index_comment' => '',
                    'columns' => [
                        'created_by' => [
                            'column_name' => 'created_by',
                            'sub_part' => null
                        ]
                    ]
                ],
                'idx_articles_language' => [
                    'table_schema' => 'dbo',
                    'table_name' => 'ww_articles',
                    'is_unique' => false,
                    'is_primary' => false,
                    'index_name' => 'idx_articles_language',
                    'index_comment' => '',
                    'columns' => [
                        'language' => [
                            'column_name' => 'language',
                            'sub_part' => null
                        ]
                    ]
                ],
                'idx_articles_page_id' => [
                    'table_schema' => 'dbo',
                    'table_name' => 'ww_articles',
                    'is_unique' => false,
                    'is_primary' => false,
                    'index_name' => 'idx_articles_page_id',
                    'index_comment' => '',
                    'columns' => [
                        'page_id' => [
                            'column_name' => 'page_id',
                            'sub_part' => null
                        ]
                    ]
                ]
            ],
            $indexes
        );
    }

    protected function setUp(): void
    {
        $this->instance = static::$db->getDriver()->getPlatform();
    }

    protected function tearDown(): void
    {
    }

    /**
     * @inheritDoc
     */
    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/../stub/metadata/' . static::$platform . '.sql');
    }
}
