<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test\Platform;

/**
 * The MySQLSchemaTest class.
 */
class MySQLPlatformTest extends AbstractPlatformTest
{
    protected static string $platform = 'MySQL';

    protected static string $driver = 'pdo_mysql';

    /**
     * Will be set at setUp()
     *
     * @var string
     */
    protected static string $schema = '';

    /**
     * @see  AbstractSchemaManager::listDatabases
     */
    public function testListDatabases(): void
    {
        $schemas = $this->instance->listDatabases();

        self::assertContains(
            self::getTestParams()['dbname'],
            $schemas
        );
    }

    /**
     * @see  AbstractSchemaManager::listSchemas
     */
    public function testListSchemas(): void
    {
        $schemas = $this->instance->listSchemas();

        self::assertContains(
            self::getTestParams()['dbname'],
            $schemas
        );
    }

    /**
     * @see  AbstractSchemaManager::listTables
     */
    public function testListTables(): void
    {
        $tables = $this->instance->listTables(static::$schema);

        self::assertEquals(
            [
                'ww_articles' => [
                    'TABLE_NAME' => 'ww_articles',
                    'TABLE_SCHEMA' => 'windwalker_test',
                    'TABLE_TYPE' => 'BASE TABLE',
                    'VIEW_DEFINITION' => null,
                    'CHECK_OPTION' => null,
                    'IS_UPDATABLE' => null,
                ],
                'ww_categories' => [
                    'TABLE_NAME' => 'ww_categories',
                    'TABLE_SCHEMA' => 'windwalker_test',
                    'TABLE_TYPE' => 'BASE TABLE',
                    'VIEW_DEFINITION' => null,
                    'CHECK_OPTION' => null,
                    'IS_UPDATABLE' => null,
                ],
            ],
            $tables
        );
    }

    /**
     * @see  AbstractSchemaManager::listViews
     */
    public function testListViews(): void
    {
        $views = $this->instance->listViews(static::$schema);

        self::assertEquals(
            [
                'ww_articles_view' => [
                    'TABLE_NAME' => 'ww_articles_view',
                    'TABLE_SCHEMA' => 'windwalker_test',
                    'TABLE_TYPE' => 'VIEW',
                    'VIEW_DEFINITION' => 'select `windwalker_test`.`ww_articles`.`id` AS `id`,`windwalker_test`.`ww_articles`.`category_id` AS `category_id`,`windwalker_test`.`ww_articles`.`page_id` AS `page_id`,`windwalker_test`.`ww_articles`.`type` AS `type`,`windwalker_test`.`ww_articles`.`price` AS `price`,`windwalker_test`.`ww_articles`.`title` AS `title`,`windwalker_test`.`ww_articles`.`alias` AS `alias`,`windwalker_test`.`ww_articles`.`introtext` AS `introtext`,`windwalker_test`.`ww_articles`.`state` AS `state`,`windwalker_test`.`ww_articles`.`ordering` AS `ordering`,`windwalker_test`.`ww_articles`.`created` AS `created`,`windwalker_test`.`ww_articles`.`created_by` AS `created_by`,`windwalker_test`.`ww_articles`.`language` AS `language`,`windwalker_test`.`ww_articles`.`params` AS `params` from `windwalker_test`.`ww_articles`',
                    'CHECK_OPTION' => 'NONE',
                    'IS_UPDATABLE' => 'YES',
                ],
            ],
            $views
        );
    }

    /**
     * @see  AbstractSchemaManager::listColumns
     */
    public function testListColumns(): void
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
                'erratas',
            ],
            array_keys($columns[array_key_first($columns)])
        );

        self::assertEquals(
            [
                'id' => [
                    'column_name' => 'id',
                    'ordinal_position' => '1',
                    'column_default' => null,
                    'is_nullable' => false,
                    'data_type' => 'int',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => '10',
                    'numeric_scale' => '0',
                    'numeric_unsigned' => true,
                    'comment' => 'Primary Index',
                    'auto_increment' => true,
                    'erratas' => [

                    ]
                ],
                'category_id' => [
                    'column_name' => 'category_id',
                    'ordinal_position' => '2',
                    'column_default' => '0',
                    'is_nullable' => false,
                    'data_type' => 'int',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => '10',
                    'numeric_scale' => '0',
                    'numeric_unsigned' => true,
                    'comment' => 'Category ID',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'page_id' => [
                    'column_name' => 'page_id',
                    'ordinal_position' => '3',
                    'column_default' => '0',
                    'is_nullable' => false,
                    'data_type' => 'int',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => '10',
                    'numeric_scale' => '0',
                    'numeric_unsigned' => true,
                    'comment' => 'Page ID',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'type' => [
                    'column_name' => 'type',
                    'ordinal_position' => '4',
                    'column_default' => 'bar',
                    'is_nullable' => false,
                    'data_type' => 'enum',
                    'character_maximum_length' => '3',
                    'character_octet_length' => '12',
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => '',
                    'auto_increment' => false,
                    'erratas' => [
                        'permitted_values' => [
                            'foo',
                            'bar',
                            'yoo'
                        ]
                    ]
                ],
                'price' => [
                    'column_name' => 'price',
                    'ordinal_position' => '5',
                    'column_default' => '0.000000',
                    'is_nullable' => true,
                    'data_type' => 'decimal',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => '20',
                    'numeric_scale' => '6',
                    'numeric_unsigned' => true,
                    'comment' => '',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'title' => [
                    'column_name' => 'title',
                    'ordinal_position' => '6',
                    'column_default' => '',
                    'is_nullable' => false,
                    'data_type' => 'varchar',
                    'character_maximum_length' => '255',
                    'character_octet_length' => '1020',
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => 'Title',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'alias' => [
                    'column_name' => 'alias',
                    'ordinal_position' => '7',
                    'column_default' => '',
                    'is_nullable' => false,
                    'data_type' => 'varchar',
                    'character_maximum_length' => '255',
                    'character_octet_length' => '1020',
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => 'Alias',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'introtext' => [
                    'column_name' => 'introtext',
                    'ordinal_position' => '8',
                    'column_default' => null,
                    'is_nullable' => false,
                    'data_type' => 'longtext',
                    'character_maximum_length' => '4294967295',
                    'character_octet_length' => '4294967295',
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => 'Intro Text',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'state' => [
                    'column_name' => 'state',
                    'ordinal_position' => '9',
                    'column_default' => '0',
                    'is_nullable' => false,
                    'data_type' => 'tinyint',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => '3',
                    'numeric_scale' => '0',
                    'numeric_unsigned' => false,
                    'comment' => '0: unpublished, 1:published',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'ordering' => [
                    'column_name' => 'ordering',
                    'ordinal_position' => '10',
                    'column_default' => '0',
                    'is_nullable' => false,
                    'data_type' => 'int',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => '10',
                    'numeric_scale' => '0',
                    'numeric_unsigned' => true,
                    'comment' => 'Ordering',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'created' => [
                    'column_name' => 'created',
                    'ordinal_position' => '11',
                    'column_default' => '1000-01-01 00:00:00',
                    'is_nullable' => false,
                    'data_type' => 'datetime',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => 'Created Date',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'created_by' => [
                    'column_name' => 'created_by',
                    'ordinal_position' => '12',
                    'column_default' => '0',
                    'is_nullable' => false,
                    'data_type' => 'int',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => '10',
                    'numeric_scale' => '0',
                    'numeric_unsigned' => true,
                    'comment' => 'Author',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'language' => [
                    'column_name' => 'language',
                    'ordinal_position' => '13',
                    'column_default' => '',
                    'is_nullable' => false,
                    'data_type' => 'char',
                    'character_maximum_length' => '7',
                    'character_octet_length' => '28',
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => 'Language',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ],
                'params' => [
                    'column_name' => 'params',
                    'ordinal_position' => '14',
                    'column_default' => null,
                    'is_nullable' => false,
                    'data_type' => 'text',
                    'character_maximum_length' => '65535',
                    'character_octet_length' => '65535',
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => 'Params',
                    'auto_increment' => false,
                    'erratas' => [

                    ]
                ]
            ],
            $columns
        );
    }

    /**
     * @see  AbstractSchemaManager::listConstraints
     */
    public function testListConstraints(): void
    {
        $constraints = $this->instance->listConstraints('#__articles', static::$schema);

        self::assertEquals(
            [
                'PRIMARY' => [
                    'constraint_name' => 'PRIMARY',
                    'constraint_type' => 'PRIMARY KEY',
                    'table_name' => 'ww_articles',
                    'columns' => [
                        'id',
                    ],
                ],
                'idx_articles_alias' => [
                    'constraint_name' => 'idx_articles_alias',
                    'constraint_type' => 'UNIQUE',
                    'table_name' => 'ww_articles',
                    'columns' => [
                        'alias',
                    ],
                ],
                'fk_articles_category_id' => [
                    'constraint_name' => 'fk_articles_category_id',
                    'constraint_type' => 'FOREIGN KEY',
                    'table_name' => 'ww_articles',
                    'columns' => [
                        'category_id',
                    ],
                    'referenced_table_schema' => 'windwalker_test',
                    'referenced_table_name' => 'ww_categories',
                    'referenced_columns' => [
                        'id',
                    ],
                    'match_option' => 'NONE',
                    'update_rule' => 'RESTRICT',
                    'delete_rule' => 'RESTRICT',
                ],
                'fk_articles_category_more' => [
                    'constraint_name' => 'fk_articles_category_more',
                    'constraint_type' => 'FOREIGN KEY',
                    'table_name' => 'ww_articles',
                    'columns' => [
                        'page_id',
                        'created_by',
                    ],
                    'referenced_table_schema' => 'windwalker_test',
                    'referenced_table_name' => 'ww_categories',
                    'referenced_columns' => [
                        'parent_id',
                        'level',
                    ],
                    'match_option' => 'NONE',
                    'update_rule' => 'RESTRICT',
                    'delete_rule' => 'RESTRICT',
                ],
            ],
            $constraints
        );
    }

    /**
     * @see  AbstractSchemaManager::listIndexes
     */
    public function testListIndexes(): void
    {
        $indexes = $this->instance->listIndexes('#__articles', static::$schema);

        self::assertEquals(
            [
                'PRIMARY' => [
                    'table_schema' => 'windwalker_test',
                    'table_name' => 'ww_articles',
                    'is_unique' => true,
                    'is_primary' => true,
                    'index_name' => 'PRIMARY',
                    'index_comment' => '',
                    'columns' => [
                        'id' => [
                            'column_name' => 'id',
                            'sub_part' => null,
                        ],
                    ],
                ],
                'idx_articles_alias' => [
                    'table_schema' => 'windwalker_test',
                    'table_name' => 'ww_articles',
                    'is_unique' => true,
                    'is_primary' => false,
                    'index_name' => 'idx_articles_alias',
                    'index_comment' => '',
                    'columns' => [
                        'alias' => [
                            'column_name' => 'alias',
                            'sub_part' => 150,
                        ],
                    ],
                ],
                'fk_articles_category_more' => [
                    'table_schema' => 'windwalker_test',
                    'table_name' => 'ww_articles',
                    'is_unique' => false,
                    'is_primary' => false,
                    'index_name' => 'fk_articles_category_more',
                    'index_comment' => '',
                    'columns' => [
                        'page_id' => [
                            'column_name' => 'page_id',
                            'sub_part' => null,
                        ],
                        'created_by' => [
                            'column_name' => 'created_by',
                            'sub_part' => null,
                        ],
                    ],
                ],
                'idx_articles_category_id' => [
                    'table_schema' => 'windwalker_test',
                    'table_name' => 'ww_articles',
                    'is_unique' => false,
                    'is_primary' => false,
                    'index_name' => 'idx_articles_category_id',
                    'index_comment' => '',
                    'columns' => [
                        'category_id' => [
                            'column_name' => 'category_id',
                            'sub_part' => null,
                        ],
                    ],
                ],
                'idx_articles_created_by' => [
                    'table_schema' => 'windwalker_test',
                    'table_name' => 'ww_articles',
                    'is_unique' => false,
                    'is_primary' => false,
                    'index_name' => 'idx_articles_created_by',
                    'index_comment' => '',
                    'columns' => [
                        'created_by' => [
                            'column_name' => 'created_by',
                            'sub_part' => null,
                        ],
                    ],
                ],
                'idx_articles_language' => [
                    'table_schema' => 'windwalker_test',
                    'table_name' => 'ww_articles',
                    'is_unique' => false,
                    'is_primary' => false,
                    'index_name' => 'idx_articles_language',
                    'index_comment' => '',
                    'columns' => [
                        'language' => [
                            'column_name' => 'language',
                            'sub_part' => null,
                        ],
                    ],
                ],
                'idx_articles_page_id' => [
                    'table_schema' => 'windwalker_test',
                    'table_name' => 'ww_articles',
                    'is_unique' => false,
                    'is_primary' => false,
                    'index_name' => 'idx_articles_page_id',
                    'index_comment' => '',
                    'columns' => [
                        'page_id' => [
                            'column_name' => 'page_id',
                            'sub_part' => null,
                        ],
                    ],
                ],
            ],
            $indexes
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        static::$schema = static::$dbname;
    }
}
