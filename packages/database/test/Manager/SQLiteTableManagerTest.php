<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager;

use Windwalker\Database\Manager\TableManager;
use Windwalker\Database\Platform\AbstractPlatform;
use Windwalker\Database\Schema\Ddl\Constraint;
use Windwalker\Database\Schema\Schema;
use Windwalker\Database\Test\AbstractDatabaseTestCase;
use Windwalker\Test\Helper\TestStringHelper;
use Windwalker\Utilities\Str;

class SQLiteTableManagerTest extends AbstractDatabaseTestCase
{
    protected static string $platform = AbstractPlatform::SQLITE;

    protected static string $driver = 'pdo_sqlite';

    protected ?TableManager $instance;

    /**
     * @see  TableManager::create
     */
    public function testCreate(): void
    {
        $table = self::$db->getTable('enterprise');

        $logs = $this->logQueries(
            fn() => $table->create(
                static function (Schema $schema) {
                    $schema->primary('id');
                    $schema->char('type')->length(25);
                    $schema->integer('catid')->nullable(true);
                    $schema->varchar('alias');
                    $schema->varchar('title')->defaultValue('H');
                    $schema->decimal('price')->length('20,6');
                    $schema->text('intro');
                    $schema->text('fulltext');
                    $schema->datetime('start_date');
                    $schema->datetime('created');
                    $schema->timestamp('updated')
                        ->onUpdateCurrent()
                        ->defaultCurrent();
                    $schema->timestamp('deleted');
                    $schema->json('params');

                    $schema->addIndex(['catid', 'type']);
                    $schema->addIndex('title(150)');
                    $schema->addUniqueKey('alias');
                }
            )
        );

        self::assertSqlFormatEquals(
            <<<SQL
            SELECT `name`       AS `TABLE_NAME`,
                   'BASE TABLE' AS TABLE_TYPE,
                   'main'       AS TABLE_SCHEMA,
                   NULL         AS VIEW_DEFINITION,
                   NULL         AS CHECK_OPTION,
                   NULL         AS IS_UPDATABLE,
                   `sql`
            FROM `main`.`sqlite_master`
            WHERE `type` = 'table'
              AND `name` NOT LIKE 'sqlite_%'
            ORDER BY `name`;
            SELECT `name` AS `TABLE_NAME`,
                   'VIEW' AS TABLE_TYPE,
                   ''     AS TABLE_SCHEMA,
                   NULL   AS VIEW_DEFINITION,
                   'NONE' AS CHECK_OPTION,
                   NULL   AS IS_UPDATABLE,
                   `sql`
            FROM `sqlite_master`
            WHERE `type` = 'view'
              AND `name` NOT LIKE 'sqlite_%'
            ORDER BY `name`;
            CREATE TABLE IF NOT EXISTS `enterprise` (
             `id` INTEGER NOT NULL,
            `type` CHAR(25) NOT NULL DEFAULT '',
            `catid` INTEGER DEFAULT NULL,
            `alias` VARCHAR(255) NOT NULL DEFAULT '',
            `title` VARCHAR(255) NOT NULL DEFAULT 'H',
            `price` DECIMAL(20,6) NOT NULL DEFAULT 0,
            `intro` text NOT NULL DEFAULT '',
            `fulltext` text NOT NULL DEFAULT '',
            `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `deleted` TIMESTAMP NOT NULL DEFAULT 1,
            `params` json NOT NULL,
            CONSTRAINT `idx_enterprise_alias` UNIQUE (`alias`),
            CONSTRAINT `pk_enterprise` PRIMARY KEY (`id` AUTOINCREMENT)
            );
            CREATE INDEX `idx_enterprise_catid_type` ON `enterprise` (`catid`,`type`);
            CREATE INDEX `idx_enterprise_title` ON `enterprise` (`title`)
            SQL,
            implode(";\n", $logs)
        );

        self::assertArrayHasKey('enterprise', $table->getPlatform()->listTables());
    }

    /**
     * @see  TableManager::getConstraints
     */
    public function testGetConstraints(): void
    {
        $constraints = $this->instance->getConstraints();
        $constraints = array_filter($constraints, fn(Constraint $item) => $item->constraintType !== 'CHECK');

        self::assertEquals(
            ['sqlite_autoindex_enterprise_1'],
            array_keys($constraints)
        );
    }

    /**
     * @see  TableManager::getDetail
     */
    public function testGetDetail(): void
    {
        $detail = $this->instance->getDetail();

        self::assertEquals(
            [
                'TABLE_NAME' => 'enterprise',
                'TABLE_SCHEMA' => 'main',
                'TABLE_TYPE' => 'BASE TABLE',
                'VIEW_DEFINITION' => null,
                'CHECK_OPTION' => null,
                'IS_UPDATABLE' => null,
                'sql' => Str::replaceCRLF(
                    <<<SQL
                    CREATE TABLE `enterprise` (
                    `id` integer NOT NULL,
                    `type` char(25) NOT NULL DEFAULT '',
                    `catid` integer DEFAULT NULL,
                    `alias` varchar(255) NOT NULL DEFAULT '',
                    `title` varchar(255) NOT NULL DEFAULT 'H',
                    `price` decimal(20,6) NOT NULL DEFAULT 0,
                    `intro` text NOT NULL DEFAULT '',
                    `fulltext` text NOT NULL DEFAULT '',
                    `start_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                    `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `deleted` timestamp NOT NULL DEFAULT 1,
                    `params` json NOT NULL,
                    CONSTRAINT `idx_enterprise_alias` UNIQUE (`alias`),
                    CONSTRAINT `pk_enterprise` PRIMARY KEY (`id` AUTOINCREMENT)
                    )
                    SQL
                ),
            ],
            $detail
        );
    }

    /**
     * @see  TableManager::update
     */
    public function testUpdate(): void
    {
        self::markTestIncomplete('Current SQLitePlatform not support schema change');
    }

    /**
     * @see  TableManager::addIndex
     */
    public function testAddIndex(): void
    {
        $logs = $this->logQueries(
            function () {
                $this->instance->addIndex('created');
                $this->instance->addIndex(['start_date', 'price']);
            }
        );

        self::assertSqlFormatEquals(
            <<<SQL
            PRAGMA table_info('enterprise');
            PRAGMA table_info('enterprise');
            PRAGMA index_list('enterprise');
            PRAGMA index_info('idx_enterprise_title');
            PRAGMA index_info('idx_enterprise_catid_type');
            PRAGMA index_info('sqlite_autoindex_enterprise_1');
            CREATE INDEX `idx_enterprise_created` ON `enterprise` (`created`);
            CREATE INDEX `idx_enterprise_start_date_price` ON `enterprise` (`start_date`,`price`)
            SQL,
            implode(";\n", $logs)
        );

        $this->instance->reset();

        self::assertEquals(
            [
                'idx_enterprise_start_date_price',
                'idx_enterprise_created',
                'idx_enterprise_title',
                'idx_enterprise_catid_type',
                'sqlite_autoindex_enterprise_1',
            ],
            array_keys($this->instance->getIndexes())
        );
    }

    /**
     * @see  TableManager::hasConstraint
     */
    public function testHasConstraint(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  TableManager::createSchemaObject
     */
    public function testGetSchemaObject(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  TableManager::hasIndex
     */
    public function testHasIndex(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  TableManager::dropIndex
     */
    public function testDropIndex(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  TableManager::addConstraint
     */
    public function testAddConstraint(): void
    {
        self::markTestIncomplete('Current SQLitePlatform not support schema change');
    }

    /**
     * @see  TableManager::dropColumn
     */
    public function testDropColumn(): void
    {
        self::markTestIncomplete('Current SQLitePlatform not support schema change');
    }

    /**
     * @see  TableManager::modifyColumn
     */
    public function testModifyColumn(): void
    {
        self::markTestIncomplete('Current SQLitePlatform not support schema change');
    }

    /**
     * @see  TableManager::save
     */
    public function testSave(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  TableManager::addColumn
     */
    public function testAddColumn(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  TableManager::truncate
     */
    public function testTruncate(): void
    {
        $logs = $this->logQueries(fn() => $this->instance->truncate());

        self::assertEquals('DELETE FROM `enterprise`', $logs[0]);
    }

    /**
     * @see  TableManager::getColumns
     */
    public function testGetColumns(): void
    {
        $cols = array_keys($this->instance->reset()->getColumns());

        self::assertEquals(
            [
                'id',
                'type',
                'catid',
                'alias',
                'title',
                'price',
                'intro',
                'fulltext',
                'start_date',
                'created',
                'updated',
                'deleted',
                'params',
            ],
            $cols
        );
    }

    /**
     * @see  TableManager::setName
     */
    public function testSetName(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  TableManager::hasColumn
     */
    public function testHasColumn(): void
    {
        self::assertTrue($this->instance->hasColumn('alias'));
        self::assertFalse($this->instance->hasColumn('enemy'));
    }

    /**
     * @see  TableManager::getColumnNames
     */
    public function testGetColumnNames(): void
    {
        self::assertEquals(
            [
                'id',
                'type',
                'catid',
                'alias',
                'title',
                'price',
                'intro',
                'fulltext',
                'start_date',
                'created',
                'updated',
                'deleted',
                'params',
            ],
            $this->instance->getColumnNames()
        );
    }

    /**
     * @see  TableManager::getIndex
     */
    public function testGetIndex(): void
    {
        $index = $this->instance->getIndex('idx_enterprise_catid_type');

        self::assertEquals(
            'idx_enterprise_catid_type',
            $index->indexName,
        );

        self::assertEquals(
            ['catid', 'type'],
            array_keys($index->getColumns())
        );
    }

    /**
     * @see  TableManager::reset
     */
    public function testReset(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  TableManager::exists
     */
    public function testExists(): void
    {
        self::assertTrue($this->instance->exists());
        self::assertFalse(self::$db->getTable('enterprise_j')->exists());
    }

    /**
     * @see  TableManager::getColumn
     */
    public function testGetColumn(): void
    {
        $column = $this->instance->reset()->getColumn('updated');

        self::assertEquals('updated', $column->columnName);
        self::assertEquals('timestamp', $column->getDataType());
    }

    /**
     * @see  TableManager::getConstraint
     */
    public function testGetConstraint(): void
    {
        self::markTestIncomplete('Currently SQLitePlatform cannot get constraints'); // TODO: Complete this test
    }

    /**
     * @see  TableManager::getIndexes
     */
    public function testGetIndexes(): void
    {
        $indexes = array_keys($this->instance->getIndexes());

        self::assertEquals(
            [
                'idx_enterprise_start_date_price',
                'idx_enterprise_created',
                'idx_enterprise_title',
                'idx_enterprise_catid_type',
                'sqlite_autoindex_enterprise_1',
            ],
            $indexes
        );
    }

    public function testGetDatabase(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    public function testGetSchema(): void
    {
        $this->instance->schemaName = $this->instance->getPlatform()::getDefaultSchema();

        $logs = $this->logQueries(fn() => $this->instance->reset()->getColumns());

        self::assertSqlFormatEquals(
            <<<SQL
            PRAGMA `main`.table_info('enterprise')
            SQL,
            $logs[0]
        );
    }

    /**
     * @see  TableManager::dropConstraint
     */
    public function testDropConstraint(): void
    {
        self::markTestIncomplete('Currently SQLitePlatform not support schema change');
    }

    public function testRenameColumn(): void
    {
        self::markTestIncomplete('Currently SQLitePlatform not support schema change');
    }

    /**
     * @see  TableManager::rename
     */
    public function testRename(): void
    {
        $newTable = $this->instance->rename('enterprise_d');

        self::assertNotSame($newTable, $this->instance);

        self::assertEquals('enterprise_d', $newTable->getName());

        self::assertTrue($newTable->exists());
        self::assertFalse($this->instance->exists());

        $this->instance = $newTable;
    }

    /**
     * @see  TableManager::drop
     */
    public function testDrop(): void
    {
        $this->instance->setName('enterprise_d')->reset();
        $this->instance->drop();

        self::assertNotContains(
            'enterprise_d',
            $this->instance->getPlatform()->listTables()
        );
    }

    /**
     * @inheritDoc
     */
    protected static function setupDatabase(): void
    {
    }

    protected function setUp(): void
    {
        $this->instance = self::$db->getTable('enterprise');
    }
}
