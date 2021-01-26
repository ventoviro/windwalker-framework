<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test\Platform;

use Windwalker\Database\Platform\MySQLPlatform;
use Windwalker\Database\Platform\SQLitePlatform;
use Windwalker\Test\Helper\TestStringHelper;
use Windwalker\Utilities\Arr;
use Windwalker\Utilities\Str;

/**
 * The SQLitePlatformTest class.
 */
class SQLitePlatformTest extends AbstractPlatformTest
{
    protected static string $platform = 'SQLite';

    protected static string $driver = 'pdo_sqlite';

    public static function getTestSchema(): string
    {
        return self::$db->getPlatform()::getDefaultSchema();
    }

    /**
     * @see  AbstractSchemaManager::listDatabases
     */
    public function testListDatabases(): void
    {
        $dbs = $this->instance->listDatabases();

        self::assertEquals(
            realpath(self::getTestParams()['database']),
            realpath($dbs[0])
        );
    }

    /**
     * @see  AbstractSchemaManager::listSchemas
     */
    public function testListSchemas(): void
    {
        $schemas = $this->instance->listSchemas();

        self::assertContains(
            'main',
            $schemas
        );
    }

    /**
     * @see  AbstractSchemaManager::listTables
     */
    public function testListTables(): void
    {
        $tables = $this->instance->listTables(static::getTestSchema());

        $tables = array_map(fn ($table) => Arr::remove($table, 'sql'), $tables);

        self::assertEquals(
            [
                'ww_articles' => [
                    'TABLE_NAME' => 'ww_articles',
                    'TABLE_TYPE' => 'BASE TABLE',
                    'TABLE_SCHEMA' => 'main',
                    'VIEW_DEFINITION' => null,
                    'CHECK_OPTION' => null,
                    'IS_UPDATABLE' => null
                ],
                'ww_categories' => [
                    'TABLE_NAME' => 'ww_categories',
                    'TABLE_TYPE' => 'BASE TABLE',
                    'TABLE_SCHEMA' => 'main',
                    'VIEW_DEFINITION' => null,
                    'CHECK_OPTION' => null,
                    'IS_UPDATABLE' => null
                ]
            ],
            $tables
        );
    }

    /**
     * @see  AbstractSchemaManager::listViews
     */
    public function testListViews(): void
    {
        $views = $this->instance->listViews(static::getTestSchema());

        $views['ww_articles_view']['sql'] = Str::replaceCRLF($views['ww_articles_view']['sql'], ' ');

        self::assertEquals(
            [
                'ww_articles_view' => [
                    'TABLE_NAME' => 'ww_articles_view',
                    'TABLE_TYPE' => 'VIEW',
                    'TABLE_SCHEMA' => 'main',
                    'VIEW_DEFINITION' => null,
                    'CHECK_OPTION' => 'NONE',
                    'IS_UPDATABLE' => null,
                    'sql' => 'CREATE VIEW `ww_articles_view` AS SELECT * FROM `ww_articles`'
                ]
            ],
            $views
        );
    }

    /**
     * @see  AbstractSchemaManager::listColumns
     */
    public function testListColumns(): void
    {
        $columns = $this->instance->listColumns('#__articles', static::getTestSchema());

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
                    'data_type' => 'integer',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => null,
                    'auto_increment' => true,
                    'erratas' => [
                        'pk' => true
                    ]
                ],
                'category_id' => [
                    'column_name' => 'category_id',
                    'ordinal_position' => 2,
                    'column_default' => '0',
                    'is_nullable' => false,
                    'data_type' => 'integer',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => null,
                    'auto_increment' => false,
                    'erratas' => [
                        'pk' => false
                    ]
                ],
                'page_id' => [
                    'column_name' => 'page_id',
                    'ordinal_position' => 3,
                    'column_default' => '0',
                    'is_nullable' => false,
                    'data_type' => 'integer',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => null,
                    'auto_increment' => false,
                    'erratas' => [
                        'pk' => false
                    ]
                ],
                'type' => [
                    'column_name' => 'type',
                    'ordinal_position' => 4,
                    'column_default' => 'bar',
                    'is_nullable' => false,
                    'data_type' => 'char',
                    'character_maximum_length' => 15,
                    'character_octet_length' => null,
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => null,
                    'auto_increment' => false,
                    'erratas' => [
                        'pk' => false
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
                    'comment' => null,
                    'auto_increment' => false,
                    'erratas' => [
                        'pk' => false
                    ]
                ],
                'title' => [
                    'column_name' => 'title',
                    'ordinal_position' => 6,
                    'column_default' => '',
                    'is_nullable' => false,
                    'data_type' => 'varchar',
                    'character_maximum_length' => 255,
                    'character_octet_length' => null,
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => null,
                    'auto_increment' => false,
                    'erratas' => [
                        'pk' => false
                    ]
                ],
                'alias' => [
                    'column_name' => 'alias',
                    'ordinal_position' => 7,
                    'column_default' => '',
                    'is_nullable' => false,
                    'data_type' => 'varchar',
                    'character_maximum_length' => 255,
                    'character_octet_length' => null,
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => null,
                    'auto_increment' => false,
                    'erratas' => [
                        'pk' => false
                    ]
                ],
                'introtext' => [
                    'column_name' => 'introtext',
                    'ordinal_position' => 8,
                    'column_default' => null,
                    'is_nullable' => false,
                    'data_type' => 'longtext',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => null,
                    'auto_increment' => false,
                    'erratas' => [
                        'pk' => false
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
                    'numeric_precision' => 1,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => null,
                    'auto_increment' => false,
                    'erratas' => [
                        'pk' => false
                    ]
                ],
                'ordering' => [
                    'column_name' => 'ordering',
                    'ordinal_position' => 10,
                    'column_default' => '0',
                    'is_nullable' => false,
                    'data_type' => 'integer',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => null,
                    'auto_increment' => false,
                    'erratas' => [
                        'pk' => false
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
                    'comment' => null,
                    'auto_increment' => false,
                    'erratas' => [
                        'pk' => false
                    ]
                ],
                'created_by' => [
                    'column_name' => 'created_by',
                    'ordinal_position' => 12,
                    'column_default' => '0',
                    'is_nullable' => false,
                    'data_type' => 'integer',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => null,
                    'auto_increment' => false,
                    'erratas' => [
                        'pk' => false
                    ]
                ],
                'language' => [
                    'column_name' => 'language',
                    'ordinal_position' => 13,
                    'column_default' => '',
                    'is_nullable' => false,
                    'data_type' => 'char',
                    'character_maximum_length' => 7,
                    'character_octet_length' => null,
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => null,
                    'auto_increment' => false,
                    'erratas' => [
                        'pk' => false
                    ]
                ],
                'params' => [
                    'column_name' => 'params',
                    'ordinal_position' => 14,
                    'column_default' => null,
                    'is_nullable' => false,
                    'data_type' => 'text',
                    'character_maximum_length' => null,
                    'character_octet_length' => null,
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                    'numeric_unsigned' => false,
                    'comment' => null,
                    'auto_increment' => false,
                    'erratas' => [
                        'pk' => false
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
        $constraints = $this->instance->listConstraints('#__articles', static::getTestSchema());

        self::assertEquals(
            [
                'idx_articles_alias' => [
                    'constraint_name' => 'idx_articles_alias',
                    'constraint_type' => 'UNIQUE',
                    'table_name' => 'ww_articles',
                    'columns' => [
                        'alias'
                    ]
                ],
                'sqlite_autoindex_ww_articles_1' => [
                    'constraint_name' => 'sqlite_autoindex_ww_articles_1',
                    'constraint_type' => 'PRIMARY KEY',
                    'table_name' => 'ww_articles',
                    'columns' => [
                        'id'
                    ]
                ]
            ],
            $constraints
        );
    }

    /**
     * @see  AbstractSchemaManager::listIndexes
     */
    public function testListIndexes(): void
    {
        $indexes = $this->instance->listIndexes('#__articles', static::getTestSchema());

        self::assertEquals(
            [
                'idx_articles_page_id' => [
                    'table_schema' => 'main',
                    'table_name' => 'ww_articles',
                    'is_unique' => false,
                    'index_name' => 'idx_articles_page_id',
                    'index_comment' => '',
                    'columns' => [
                        'page_id' => [
                            'column_name' => 'page_id',
                            'subpart' => null
                        ]
                    ]
                ],
                'idx_articles_language' => [
                    'table_schema' => 'main',
                    'table_name' => 'ww_articles',
                    'is_unique' => false,
                    'index_name' => 'idx_articles_language',
                    'index_comment' => '',
                    'columns' => [
                        'language' => [
                            'column_name' => 'language',
                            'subpart' => null
                        ]
                    ]
                ],
                'idx_articles_created_by' => [
                    'table_schema' => 'main',
                    'table_name' => 'ww_articles',
                    'is_unique' => false,
                    'index_name' => 'idx_articles_created_by',
                    'index_comment' => '',
                    'columns' => [
                        'created_by' => [
                            'column_name' => 'created_by',
                            'subpart' => null
                        ]
                    ]
                ],
                'idx_articles_category_id' => [
                    'table_schema' => 'main',
                    'table_name' => 'ww_articles',
                    'is_unique' => false,
                    'index_name' => 'idx_articles_category_id',
                    'index_comment' => '',
                    'columns' => [
                        'category_id' => [
                            'column_name' => 'category_id',
                            'subpart' => null
                        ]
                    ]
                ],
                'idx_articles_alias' => [
                    'table_schema' => 'main',
                    'table_name' => 'ww_articles',
                    'is_unique' => true,
                    'index_name' => 'idx_articles_alias',
                    'index_comment' => '',
                    'columns' => [
                        'alias' => [
                            'column_name' => 'alias',
                            'subpart' => null
                        ]
                    ]
                ],
                'sqlite_autoindex_ww_articles_1' => [
                    'table_schema' => 'main',
                    'table_name' => 'ww_articles',
                    'is_unique' => true,
                    'index_name' => 'sqlite_autoindex_ww_articles_1',
                    'index_comment' => '',
                    'columns' => [
                        'id' => [
                            'column_name' => 'id',
                            'subpart' => null
                        ]
                    ]
                ]
            ],
            $indexes
        );
    }

    public function testGetCurrentDatabase(): void
    {
        $file = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, self::$db->getOption('database'));

        self::assertEquals(
            $file,
            Str::intersectRight($file, $this->instance->getCurrentDatabase())
        );
    }

    public function testCreateDropDatabase(): void
    {
        $file = __DIR__ . '/../../tmp/hello.db';

        if (in_array('hello', $this->instance->listSchemas(), true)) {
            $this->instance->dropSchema('hello', []);
        }

        $this->instance->createDatabase($file, ['as' => 'hello']);

        self::assertContains(
            'hello',
            $this->instance->listSchemas()
        );

        $this->instance->dropDatabase('hello');

        self::assertNotContains(
            'hello',
            $this->instance->listSchemas()
        );
    }

    public function testCreateDropSchema(): void
    {
        self::markTestSkipped();
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
