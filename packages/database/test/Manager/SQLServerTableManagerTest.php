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
use Windwalker\Database\Schema\Ddl\Constraint;
use Windwalker\Database\Schema\Schema;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

class SQLServerTableManagerTest extends AbstractDatabaseTestCase
{
    protected static string $platform = 'SQLServer';

    protected static string $driver = 'pdo_sqlsrv';

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
            SELECT [TABLE_NAME],
                   [TABLE_SCHEMA],
                   [TABLE_TYPE],
                   NULL AS VIEW_DEFINITION,
                   NULL AS CHECK_OPTION,
                   NULL AS IS_UPDATABLE
            FROM [INFORMATION_SCHEMA].[TABLES]
            WHERE [TABLE_TYPE] = 'BASE TABLE'
              AND [TABLE_SCHEMA] != 'INFORMATION_SCHEMA'
            ORDER BY [TABLE_NAME];
            SELECT [TABLE_NAME],
                   [TABLE_SCHEMA],
                   'VIEW' AS TABLE_TYPE,
                   [VIEW_DEFINITION],
                   [CHECK_OPTION],
                   [IS_UPDATABLE]
            FROM [INFORMATION_SCHEMA].[VIEWS]
            WHERE [TABLE_SCHEMA] != 'INFORMATION_SCHEMA'
            ORDER BY [TABLE_NAME];
            CREATE TABLE [enterprise]
            (
                [id] INT NOT NULL IDENTITY,
                [TYPE] NCHAR(25) NOT NULL DEFAULT 0,
                [catid] INT DEFAULT NULL,
                [alias] nvarchar(255) NOT NULL DEFAULT 0,
                [title] nvarchar(255) NOT NULL DEFAULT 'H',
                [price] DECIMAL(20,6) NOT NULL DEFAULT 0,
                [intro] nvarchar(MAX) NOT NULL DEFAULT 0,
                [fulltext] nvarchar(MAX) NOT NULL DEFAULT 0,
                [start_date] datetime2 NOT NULL DEFAULT '1900-01-01 00:00:00',
                [created] datetime2 NOT NULL DEFAULT '1900-01-01 00:00:00',
                [updated] datetime2 NOT NULL DEFAULT CURRENT_TIMESTAMP,
                [deleted] datetime2 NOT NULL DEFAULT '1900-01-01 00:00:00',
                [params] nvarchar(MAX) NOT NULL DEFAULT 0
            );
            ALTER TABLE [enterprise]
                ADD CONSTRAINT [pk_enterprise] PRIMARY KEY ([id]);
            CREATE INDEX [idx_enterprise_catid_type] ON [enterprise] ([catid], [TYPE]);
            CREATE INDEX [idx_enterprise_title] ON [enterprise] ([title]);
            ALTER TABLE [enterprise]
                ADD CONSTRAINT [idx_enterprise_alias] UNIQUE ([alias])
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
            ['pk_enterprise', 'idx_enterprise_alias'],
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
                'TABLE_SCHEMA' => 'dbo',
                'TABLE_TYPE' => 'BASE TABLE',
                'VIEW_DEFINITION' => null,
                'CHECK_OPTION' => null,
                'IS_UPDATABLE' => null,
            ],
            $detail
        );
    }

    /**
     * @see  TableManager::update
     */
    public function testUpdate(): void
    {
        $logs = $this->logQueries(
            fn() => $this->instance->update(
                function (Schema $schema) {
                    // New column
                    $schema->varchar('captain')->length(512)->after('catid');
                    $schema->varchar('first_officer')->length(512)->after('captain');

                    // Update column
                    $schema->char('alias')->length(25)
                        ->nullable(true)
                        ->defaultValue('');

                    // New index
                    $schema->addIndex('captain');
                }
            )
        );

        // phpcs:disable
        self::assertSqlFormatEquals(
            <<<SQL
            SELECT [C].[ORDINAL_POSITION],
                   [C].[COLUMN_DEFAULT],
                   [C].[IS_NULLABLE],
                   [C].[DATA_TYPE],
                   [C].[CHARACTER_MAXIMUM_LENGTH],
                   [C].[CHARACTER_OCTET_LENGTH],
                   [C].[NUMERIC_PRECISION],
                   [C].[NUMERIC_SCALE],
                   [C].[COLUMN_NAME],
                   [sc].[is_identity]
            FROM [INFORMATION_SCHEMA].[COLUMNS] AS [C]
                     LEFT JOIN [sys].[columns] AS [sc] ON [sc].[object_id] = object_id(C.TABLE_NAME)
                AND [sc].[NAME] = [C].[COLUMN_NAME]
            WHERE [TABLE_NAME] = 'enterprise'
              AND [TABLE_SCHEMA] != 'INFORMATION_SCHEMA';
            ALTER TABLE
                [enterprise]
                ADD
                    [captain] nvarchar(512) NOT NULL DEFAULT 0;
            ALTER TABLE
                [enterprise]
                ADD
                    [first_officer] nvarchar(512) NOT NULL DEFAULT 0;
            DECLARE @ConstraintName nvarchar(200)
            SELECT @ConstraintName = Name
            FROM SYS.DEFAULT_CONSTRAINTS
            WHERE PARENT_OBJECT_ID = OBJECT_ID('enterprise')
              AND PARENT_COLUMN_ID = (
                SELECT column_id
                FROM sys.columns
                WHERE NAME = N 'alias'
                  AND object_id = OBJECT_ID(N 'enterprise')
            )
            IF @ConstraintName IS NOT NULL
                EXEC(
                    'ALTER TABLE [enterprise] DROP CONSTRAINT [' + @ConstraintName + ']'
                    );
            SELECT [T].[TABLE_NAME],
                   [TC].[CONSTRAINT_NAME],
                   [TC].[CONSTRAINT_TYPE],
                   [KCU].[COLUMN_NAME],
                   [CC].[CHECK_CLAUSE],
                   [RC].[MATCH_OPTION],
                   [RC].[UPDATE_RULE],
                   [RC].[DELETE_RULE],
                   [KCU2].[TABLE_SCHEMA] AS [REFERENCED_TABLE_SCHEMA],
                   [KCU2].[TABLE_NAME]   AS [REFERENCED_TABLE_NAME],
                   [KCU2].[COLUMN_NAME]  AS [REFERENCED_COLUMN_NAME]
            FROM [INFORMATION_SCHEMA].[TABLES] AS [T]
                     INNER JOIN [INFORMATION_SCHEMA].[TABLE_CONSTRAINTS] AS [TC] ON [T].[TABLE_SCHEMA] = [TC].[TABLE_SCHEMA]
                AND [T].[TABLE_NAME] = [TC].[TABLE_NAME]
                     LEFT JOIN [INFORMATION_SCHEMA].[KEY_COLUMN_USAGE] AS [KCU] ON [KCU].[TABLE_SCHEMA] = [TC].[TABLE_SCHEMA]
                AND [KCU].[TABLE_NAME] = [TC].[TABLE_NAME]
                AND [KCU].[CONSTRAINT_NAME] = [TC].[CONSTRAINT_NAME]
                     LEFT JOIN [INFORMATION_SCHEMA].[CHECK_CONSTRAINTS] AS [CC]
                               ON [CC].[CONSTRAINT_SCHEMA] = [TC].[CONSTRAINT_SCHEMA]
                                   AND [CC].[CONSTRAINT_NAME] = [TC].[CONSTRAINT_NAME]
                     LEFT JOIN [INFORMATION_SCHEMA].[REFERENTIAL_CONSTRAINTS] AS [RC]
                               ON [RC].[CONSTRAINT_SCHEMA] = [TC].[CONSTRAINT_SCHEMA]
                                   AND [RC].[CONSTRAINT_NAME] = [TC].[CONSTRAINT_NAME]
                     LEFT JOIN [INFORMATION_SCHEMA].[KEY_COLUMN_USAGE] AS [KCU2]
                               ON [RC].[UNIQUE_CONSTRAINT_SCHEMA] = [KCU2].[CONSTRAINT_SCHEMA]
                                   AND [RC].[UNIQUE_CONSTRAINT_NAME] = [KCU2].[CONSTRAINT_NAME]
                                   AND [KCU].[ORDINAL_POSITION] = [KCU2].[ORDINAL_POSITION]
            WHERE [T].[TABLE_NAME] = 'enterprise'
              AND [T].[TABLE_TYPE] IN ('BASE table', 'VIEW')
              AND [T].[TABLE_SCHEMA] NOT IN (
                                             'PG_CATALOG', 'INFORMATION_SCHEMA'
                )
            ORDER BY CASE [TC].[CONSTRAINT_TYPE]
                         WHEN 'PRIMARY KEY' THEN 1
                         WHEN 'UNIQUE' THEN 2
                         WHEN 'FOREIGN KEY' THEN 3
                         WHEN 'CHECK' THEN 4
                         ELSE 5 END,
                     [TC].[CONSTRAINT_NAME],
                     [KCU].[ORDINAL_POSITION];
            ALTER TABLE
                [enterprise]
                DROP
                    CONSTRAINT [idx_enterprise_alias];
            SELECT schema_name(tbl.schema_id) AS schema_name,
                   [tbl].[NAME]               AS [TABLE_NAME],
                   [col].[NAME]               AS [COLUMN_NAME],
                   [idx].[NAME]               AS [index_name],
                   [col].*,
                   [idx].*
            FROM [sys].[columns] AS [col]
                     LEFT JOIN [sys].[tables] AS [tbl] ON [col].[object_id] = [tbl].[object_id]
                     LEFT JOIN [sys].[index_columns] AS [ic] ON [col].[column_id] = [ic].[column_id]
                AND [ic].[object_id] = [tbl].[object_id]
                     LEFT JOIN [sys].[indexes] AS [idx] ON [idx].[object_id] = [tbl].[object_id]
                AND [idx].[index_id] = [ic].[index_id]
            WHERE [tbl].[NAME] = 'enterprise'
              AND (
                    [idx].[NAME] IS NOT NULL
                    OR [col].[is_identity] = 1
                    OR [idx].[is_primary_key] = 1
                );
            ALTER TABLE
                [enterprise]
                ALTER COLUMN [alias] NCHAR(25) NOT NULL;
            ALTER TABLE
                [enterprise]
                ADD
                    DEFAULT '' FOR [alias];
            SELECT schema_name(tbl.schema_id) AS schema_name,
                   [tbl].[NAME]               AS [TABLE_NAME],
                   [col].[NAME]               AS [COLUMN_NAME],
                   [idx].[NAME]               AS [index_name],
                   [col].*,
                   [idx].*
            FROM [sys].[columns] AS [col]
                     LEFT JOIN [sys].[tables] AS [tbl] ON [col].[object_id] = [tbl].[object_id]
                     LEFT JOIN [sys].[index_columns] AS [ic] ON [col].[column_id] = [ic].[column_id]
                AND [ic].[object_id] = [tbl].[object_id]
                     LEFT JOIN [sys].[indexes] AS [idx] ON [idx].[object_id] = [tbl].[object_id]
                AND [idx].[index_id] = [ic].[index_id]
            WHERE [tbl].[NAME] = 'enterprise'
              AND (
                    [idx].[NAME] IS NOT NULL
                    OR [col].[is_identity] = 1
                    OR [idx].[is_primary_key] = 1
                );
            CREATE INDEX [idx_enterprise_captain] ON [enterprise] ([captain])
            SQL,
            implode("\n;", $logs)
        );
        // phpcs:enable
    }

    /**
     * @see  TableManager::addIndex
     */
    public function testAddIndex(): void
    {
        $logs = $this->logQueries(
            function () {
                $this->instance->addIndex('created');
                $this->instance->addIndex(['start_date', 'title']);
            }
        );

        self::assertSqlFormatEquals(
            <<<SQL
            SELECT [C].[ORDINAL_POSITION],
                   [C].[COLUMN_DEFAULT],
                   [C].[IS_NULLABLE],
                   [C].[DATA_TYPE],
                   [C].[CHARACTER_MAXIMUM_LENGTH],
                   [C].[CHARACTER_OCTET_LENGTH],
                   [C].[NUMERIC_PRECISION],
                   [C].[NUMERIC_SCALE],
                   [C].[COLUMN_NAME],
                   [sc].[is_identity]
            FROM [INFORMATION_SCHEMA].[COLUMNS] AS [C]
                     LEFT JOIN [sys].[columns] AS [sc] ON [sc].[object_id] = object_id(C.TABLE_NAME)
                AND [sc].[NAME] = [C].[COLUMN_NAME]
            WHERE [TABLE_NAME] = 'enterprise'
              AND [TABLE_SCHEMA] != 'INFORMATION_SCHEMA';
            SELECT schema_name(tbl.schema_id) AS schema_name,
                   [tbl].[NAME]               AS [TABLE_NAME],
                   [col].[NAME]               AS [COLUMN_NAME],
                   [idx].[NAME]               AS [index_name],
                   [col].*,
                   [idx].*
            FROM [sys].[columns] AS [col]
                     LEFT JOIN [sys].[tables] AS [tbl] ON [col].[object_id] = [tbl].[object_id]
                     LEFT JOIN [sys].[index_columns] AS [ic] ON [col].[column_id] = [ic].[column_id]
                AND [ic].[object_id] = [tbl].[object_id]
                     LEFT JOIN [sys].[indexes] AS [idx] ON [idx].[object_id] = [tbl].[object_id]
                AND [idx].[index_id] = [ic].[index_id]
            WHERE [tbl].[NAME] = 'enterprise'
              AND (
                    [idx].[NAME] IS NOT NULL
                    OR [col].[is_identity] = 1
                    OR [idx].[is_primary_key] = 1
                );
            CREATE INDEX [idx_enterprise_created] ON [enterprise] ([created]);
            CREATE INDEX [idx_enterprise_start_date_title] ON [enterprise] ([start_date], [title])
            SQL,
            implode(";\n", $logs)
        );

        $this->instance->reset();

        self::assertEquals(
            [
                'pk_enterprise',
                'idx_enterprise_catid_type',
                'idx_enterprise_title',
                'idx_enterprise_start_date_title',
                'idx_enterprise_created',
                'idx_enterprise_captain',
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
        $logs = $this->logQueries(
            fn() => $this->instance->addConstraint(
                ['captain', 'first_officer'],
                Constraint::TYPE_UNIQUE
            )
        );

        // phpcs:disable
        self::assertSqlFormatEquals(
            <<<SQL
            SELECT [C].[ORDINAL_POSITION],
                   [C].[COLUMN_DEFAULT],
                   [C].[IS_NULLABLE],
                   [C].[DATA_TYPE],
                   [C].[CHARACTER_MAXIMUM_LENGTH],
                   [C].[CHARACTER_OCTET_LENGTH],
                   [C].[NUMERIC_PRECISION],
                   [C].[NUMERIC_SCALE],
                   [C].[COLUMN_NAME],
                   [sc].[is_identity]
            FROM [INFORMATION_SCHEMA].[COLUMNS] AS [C]
                     LEFT JOIN [sys].[columns] AS [sc] ON [sc].[object_id] = object_id(C.TABLE_NAME)
                AND [sc].[NAME] = [C].[COLUMN_NAME]
            WHERE [TABLE_NAME] = 'enterprise'
              AND [TABLE_SCHEMA] != 'INFORMATION_SCHEMA';
            SELECT [T].[TABLE_NAME],
                   [TC].[CONSTRAINT_NAME],
                   [TC].[CONSTRAINT_TYPE],
                   [KCU].[COLUMN_NAME],
                   [CC].[CHECK_CLAUSE],
                   [RC].[MATCH_OPTION],
                   [RC].[UPDATE_RULE],
                   [RC].[DELETE_RULE],
                   [KCU2].[TABLE_SCHEMA] AS [REFERENCED_TABLE_SCHEMA],
                   [KCU2].[TABLE_NAME]   AS [REFERENCED_TABLE_NAME],
                   [KCU2].[COLUMN_NAME]  AS [REFERENCED_COLUMN_NAME]
            FROM [INFORMATION_SCHEMA].[TABLES] AS [T]
                     INNER JOIN [INFORMATION_SCHEMA].[TABLE_CONSTRAINTS] AS [TC] ON [T].[TABLE_SCHEMA] = [TC].[TABLE_SCHEMA]
                AND [T].[TABLE_NAME] = [TC].[TABLE_NAME]
                     LEFT JOIN [INFORMATION_SCHEMA].[KEY_COLUMN_USAGE] AS [KCU] ON [KCU].[TABLE_SCHEMA] = [TC].[TABLE_SCHEMA]
                AND [KCU].[TABLE_NAME] = [TC].[TABLE_NAME]
                AND [KCU].[CONSTRAINT_NAME] = [TC].[CONSTRAINT_NAME]
                     LEFT JOIN [INFORMATION_SCHEMA].[CHECK_CONSTRAINTS] AS [CC]
                               ON [CC].[CONSTRAINT_SCHEMA] = [TC].[CONSTRAINT_SCHEMA]
                                   AND [CC].[CONSTRAINT_NAME] = [TC].[CONSTRAINT_NAME]
                     LEFT JOIN [INFORMATION_SCHEMA].[REFERENTIAL_CONSTRAINTS] AS [RC]
                               ON [RC].[CONSTRAINT_SCHEMA] = [TC].[CONSTRAINT_SCHEMA]
                                   AND [RC].[CONSTRAINT_NAME] = [TC].[CONSTRAINT_NAME]
                     LEFT JOIN [INFORMATION_SCHEMA].[KEY_COLUMN_USAGE] AS [KCU2]
                               ON [RC].[UNIQUE_CONSTRAINT_SCHEMA] = [KCU2].[CONSTRAINT_SCHEMA]
                                   AND [RC].[UNIQUE_CONSTRAINT_NAME] = [KCU2].[CONSTRAINT_NAME]
                                   AND [KCU].[ORDINAL_POSITION] = [KCU2].[ORDINAL_POSITION]
            WHERE [T].[TABLE_NAME] = 'enterprise'
              AND [T].[TABLE_TYPE] IN ('BASE table', 'VIEW')
              AND [T].[TABLE_SCHEMA] NOT IN (
                                             'PG_CATALOG', 'INFORMATION_SCHEMA'
                )
            ORDER BY CASE [TC].[CONSTRAINT_TYPE]
                         WHEN 'PRIMARY KEY' THEN 1
                         WHEN 'UNIQUE' THEN 2
                         WHEN 'FOREIGN KEY' THEN 3
                         WHEN 'CHECK' THEN 4
                         ELSE 5 END,
                     [TC].[CONSTRAINT_NAME],
                     [KCU].[ORDINAL_POSITION];
            ALTER TABLE
                [enterprise]
                ADD
                    CONSTRAINT [ct_enterprise_captain_first_officer] UNIQUE ([captain], [first_officer])
            SQL,
            implode(";\n", $logs)
        );
        // phpcs:enable
    }

    /**
     * @see  TableManager::dropColumn
     */
    public function testDropColumn(): void
    {
        $this->instance->reset();
        $this->instance->dropColumn(['captain', 'first_officer']);

        $this->instance->reset();

        self::assertFalse($this->instance->hasColumn('captain'));
        self::assertFalse($this->instance->hasColumn('first_officer'));
    }

    /**
     * @see  TableManager::modifyColumn
     */
    public function testModifyColumn(): void
    {
        $this->instance->modifyColumn(
            'price',
            'decimal(15,4)',
            true,
            100.5
        );

        $this->instance->reset();
        $column = $this->instance->getColumn('price');

        self::assertEquals('15,4', $column->getLengthExpression());
        self::assertEquals('decimal', $column->getDataType());
        self::assertEquals(100.5, $column->getColumnDefault());
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

        self::assertEquals('TRUNCATE TABLE [enterprise]', $logs[0]);
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
        $index = $this->instance->getIndex('idx_enterprise_start_date_title');

        self::assertEquals(
            'idx_enterprise_start_date_title',
            $index->indexName,
        );

        self::assertEquals(
            ['title', 'start_date'],
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
        self::assertEquals('datetime2', $column->getDataType());
    }

    /**
     * @see  TableManager::getConstraint
     */
    public function testGetConstraint(): void
    {
        $this->instance->addConstraint(
            ['alias'],
            Constraint::TYPE_UNIQUE,
            'const_enterprise_alias',
        );

        $constraint = $this->instance->reset()->getConstraint('const_enterprise_alias');

        self::assertEquals(
            'const_enterprise_alias',
            $constraint->constraintName
        );

        self::assertEquals(['alias'], array_keys($constraint->getColumns()));
    }

    /**
     * @see  TableManager::getIndexes
     */
    public function testGetIndexes(): void
    {
        $indexes = array_keys($this->instance->getIndexes());

        self::assertEquals(
            [
                'pk_enterprise',
                'idx_enterprise_catid_type',
                'const_enterprise_alias',
                'idx_enterprise_title',
                'idx_enterprise_start_date_title',
                'idx_enterprise_created',
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
        $this->instance->schemaName = 'dbo';

        $logs = $this->logQueries(fn() => $this->instance->getColumns());

        self::assertSqlFormatEquals(
            <<<SQL
            SELECT [C].[ORDINAL_POSITION],
                   [C].[COLUMN_DEFAULT],
                   [C].[IS_NULLABLE],
                   [C].[DATA_TYPE],
                   [C].[CHARACTER_MAXIMUM_LENGTH],
                   [C].[CHARACTER_OCTET_LENGTH],
                   [C].[NUMERIC_PRECISION],
                   [C].[NUMERIC_SCALE],
                   [C].[COLUMN_NAME],
                   [sc].[is_identity]
            FROM [INFORMATION_SCHEMA].[COLUMNS] AS [C]
                     LEFT JOIN [sys].[columns] AS [sc] ON [sc].[object_id] = object_id(C.TABLE_NAME)
                AND [sc].[NAME] = [C].[COLUMN_NAME]
            WHERE [TABLE_NAME] = 'enterprise'
              AND [TABLE_SCHEMA] = 'dbo'
            SQL,
            $logs[0]
        );
    }

    /**
     * @see  TableManager::dropConstraint
     */
    public function testDropConstraint(): void
    {
        $this->instance->dropConstraint('const_enterprise_alias');

        self::assertEquals(
            ['pk_enterprise'],
            array_keys($this->instance->reset()->getConstraints())
        );
    }

    public function testRenameColumn(): void
    {
        self::markTestIncomplete();
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
