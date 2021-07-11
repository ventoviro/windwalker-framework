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

class PostgreSQLTableManagerTest extends AbstractDatabaseTestCase
{
    protected static string $platform = 'PostgreSQL';

    protected static string $driver = 'pdo_pgsql';

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
            SELECT "table_name"    AS "TABLE_NAME",
                   "table_catalog" AS "TABLE_CATALOG",
                   "table_schema"  AS "TABLE_SCHEMA",
                   "table_type"    AS "TABLE_TYPE",
                   NULL            AS "VIEW_DEFINITION",
                   NULL            AS "CHECK_OPTION",
                   NULL            AS "IS_UPDATABLE"
            FROM "information_schema"."tables"
            WHERE "table_type" = 'BASE TABLE'
              AND "table_schema" NOT IN ('pg_catalog', 'information_schema')
            ORDER BY "table_name" ASC;
            SELECT "table_name"      AS "TABLE_NAME",
                   "table_catalog"   AS "TABLE_CATALOG",
                   "table_schema"    AS "TABLE_SCHEMA",
                   'VIEW'            AS "TABLE_TYPE",
                   "view_definition" AS "VIEW_DEFINITION",
                   "check_option"    AS "CHECK_OPTION",
                   "is_updatable"    AS "IS_UPDATABLE"
            FROM "information_schema"."views"
            WHERE "table_schema" NOT IN ('pg_catalog', 'information_schema')
            ORDER BY "table_name" ASC;
            CREATE TABLE IF NOT EXISTS "enterprise" (
            "id" serial NOT NULL,
            "type" CHAR(25) NOT NULL DEFAULT '',
            "catid" INTEGER DEFAULT NULL,
            "alias" VARCHAR(255) NOT NULL DEFAULT '',
            "title" VARCHAR(255) NOT NULL DEFAULT 'H',
            "price" DECIMAL(20,6) NOT NULL DEFAULT 0,
            "intro" text NOT NULL DEFAULT '',
            "fulltext" text NOT NULL DEFAULT '',
            "start_date" TIMESTAMP NOT NULL DEFAULT '1970-01-01 00:00:00',
            "created" TIMESTAMP NOT NULL DEFAULT '1970-01-01 00:00:00',
            "updated" TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            "deleted" TIMESTAMP NOT NULL DEFAULT '1970-01-01 00:00:00',
            "params" json NOT NULL
            );
            ALTER TABLE "enterprise"
             ADD CONSTRAINT "pk_enterprise" PRIMARY KEY ("id");
            CREATE INDEX "idx_enterprise_catid_type" ON "enterprise" ("catid","type");
            CREATE INDEX "idx_enterprise_title" ON "enterprise" ("title");
            ALTER TABLE "enterprise"
             ADD CONSTRAINT "idx_enterprise_alias" UNIQUE ("alias")
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
                'TABLE_SCHEMA' => 'public',
                'TABLE_TYPE' => 'BASE TABLE',
                'TABLE_CATALOG' => 'windwalker_test',
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
            SELECT "ordinal_position",
                   "column_default",
                   "is_nullable",
                   "data_type",
                   "character_maximum_length",
                   "character_octet_length",
                   "numeric_precision",
                   "numeric_scale",
                   "column_name"
            FROM "information_schema"."columns"
            WHERE "table_name" = 'enterprise'
              AND "table_schema" NOT IN ('pg_catalog', 'information_schema');
            ALTER TABLE "enterprise"
                ADD COLUMN "captain" varchar(512) NOT NULL DEFAULT '';
            ALTER TABLE "enterprise"
                ADD COLUMN "first_officer" varchar(512) NOT NULL DEFAULT '';
            ALTER TABLE "enterprise"
                ALTER COLUMN "alias" TYPE CHAR(25),
                ALTER COLUMN "alias" SET NOT NULL,
                ALTER COLUMN "alias" SET DEFAULT '';
            SELECT "ix".*, tc.constraint_type = 'PRIMARY KEY' AS "is_primary"
            FROM "pg_indexes" AS "ix"
                     LEFT JOIN "information_schema"."table_constraints" AS "tc"
                               ON "tc"."table_schema" = "ix"."schemaname" AND "tc"."constraint_name" = "ix"."indexname" AND
                                  tc.constraint_type = 'PRIMARY KEY'
            WHERE "tablename" = 'enterprise'
              AND "schemaname" NOT IN ('pg_catalog', 'information_schema')
            ORDER BY CASE tc.constraint_type WHEN 'PRIMARY KEY' THEN 1 ELSE 2 END;
            CREATE INDEX "idx_enterprise_captain" ON "enterprise" ("captain")
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
                $this->instance->addIndex(['start_date', 'first_officer']);
            }
        );

        // phpcs:disable
        self::assertSqlFormatEquals(
            <<<SQL
            SELECT "ordinal_position",
                   "column_default",
                   "is_nullable",
                   "data_type",
                   "character_maximum_length",
                   "character_octet_length",
                   "numeric_precision",
                   "numeric_scale",
                   "column_name"
            FROM "information_schema"."columns"
            WHERE "table_name" = 'enterprise'
              AND "table_schema" NOT IN ('pg_catalog', 'information_schema');
            SELECT "ix".*, tc.constraint_type = 'PRIMARY KEY' AS "is_primary"
            FROM "pg_indexes" AS "ix"
                     LEFT JOIN "information_schema"."table_constraints" AS "tc"
                               ON "tc"."table_schema" = "ix"."schemaname" AND "tc"."constraint_name" = "ix"."indexname" AND
                                  tc.constraint_type = 'PRIMARY KEY'
            WHERE "tablename" = 'enterprise'
              AND "schemaname" NOT IN ('pg_catalog', 'information_schema')
            ORDER BY CASE tc.constraint_type WHEN 'PRIMARY KEY' THEN 1 ELSE 2 END;
            CREATE INDEX "idx_enterprise_created" ON "enterprise" ("created");
            CREATE INDEX "idx_enterprise_start_date_first_officer" ON "enterprise" ("start_date", "first_officer")
            SQL,
            implode(";\n", $logs)
        );
        // phpcs:enable

        $this->instance->reset();

        self::assertArraySimilar(
            [
                'pk_enterprise',
                'idx_enterprise_alias',
                'idx_enterprise_catid_type',
                'idx_enterprise_title',
                'idx_enterprise_captain',
                'idx_enterprise_created',
                'idx_enterprise_start_date_first_officer',
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
            SELECT "ordinal_position",
                   "column_default",
                   "is_nullable",
                   "data_type",
                   "character_maximum_length",
                   "character_octet_length",
                   "numeric_precision",
                   "numeric_scale",
                   "column_name"
            FROM "information_schema"."columns"
            WHERE "table_name" = 'enterprise'
              AND "table_schema" NOT IN ('pg_catalog', 'information_schema');
            SELECT "t"."table_name",
                   "tc"."constraint_name",
                   "tc"."constraint_type",
                   "kcu"."column_name",
                   "cc"."check_clause",
                   "rc"."match_option",
                   "rc"."update_rule",
                   "rc"."delete_rule",
                   "kcu2"."table_schema" AS "referenced_table_schema",
                   "kcu2"."table_name"   AS "referenced_table_name",
                   "kcu2"."column_name"  AS "referenced_column_name"
            FROM "information_schema"."tables" AS "t"
                     INNER JOIN "information_schema"."table_constraints" AS "tc"
                                ON "t"."table_schema" = "tc"."table_schema" AND "t"."table_name" = "tc"."table_name"
                     LEFT JOIN "information_schema"."key_column_usage" AS "kcu"
                               ON "kcu"."table_schema" = "tc"."table_schema" AND "kcu"."table_name" = "tc"."table_name" AND
                                  "kcu"."constraint_name" = "tc"."constraint_name"
                     LEFT JOIN "information_schema"."check_constraints" AS "cc"
                               ON "cc"."constraint_schema" = "tc"."constraint_schema" AND
                                  "cc"."constraint_name" = "tc"."constraint_name"
                     LEFT JOIN "information_schema"."referential_constraints" AS "rc"
                               ON "rc"."constraint_schema" = "tc"."constraint_schema" AND
                                  "rc"."constraint_name" = "tc"."constraint_name"
                     LEFT JOIN "information_schema"."key_column_usage" AS "kcu2"
                               ON "rc"."unique_constraint_schema" = "kcu2"."constraint_schema" AND
                                  "rc"."unique_constraint_name" = "kcu2"."constraint_name" AND
                                  "kcu"."position_in_unique_constraint" = "kcu2"."ordinal_position"
            WHERE "t"."table_name" = 'enterprise'
              AND "t"."table_type" IN ('BASE TABLE', 'VIEW')
              AND "t"."table_schema" NOT IN ('pg_catalog', 'information_schema')
            ORDER BY CASE "tc"."constraint_type"
                         WHEN 'PRIMARY KEY' THEN 1
                         WHEN 'UNIQUE' THEN 2
                         WHEN 'FOREIGN KEY' THEN 3
                         WHEN 'CHECK' THEN 4
                         ELSE 5 END, "tc"."constraint_name", "kcu"."ordinal_position";
            ALTER TABLE "enterprise"
                ADD CONSTRAINT "ct_enterprise_captain_first_officer" UNIQUE ("captain", "first_officer")
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
        self::assertEquals('numeric', $column->getDataType());
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

        self::assertEquals('TRUNCATE TABLE "enterprise"', $logs[0]);
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
        self::assertEquals('timestamp without time zone', $column->getDataType());
    }

    /**
     * @see  TableManager::getConstraint
     */
    public function testGetConstraint(): void
    {
        $constraint = $this->instance->reset()->getConstraint('idx_enterprise_alias');

        self::assertEquals(
            'idx_enterprise_alias',
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
                'idx_enterprise_title',
                'idx_enterprise_alias',
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
        $this->instance->schemaName = $this->instance->getPlatform()::getDefaultSchema();

        $logs = $this->logQueries(fn() => $this->instance->getColumns());

        self::assertSqlFormatEquals(
            <<<SQL
            SELECT
              "ordinal_position",
              "column_default",
              "is_nullable",
              "data_type",
              "character_maximum_length",
              "character_octet_length",
              "numeric_precision",
              "numeric_scale",
              "column_name"
            FROM
              "information_schema"."columns"
            WHERE
              "table_name" = 'enterprise'
              AND "table_schema" = 'public'
            SQL,
            $logs[0]
        );
    }

    /**
     * @see  TableManager::dropConstraint
     */
    public function testDropConstraint(): void
    {
        $this->instance->dropConstraint('idx_enterprise_alias');

        self::assertEquals(
            ['pk_enterprise'],
            array_keys(
                array_filter(
                    $this->instance->reset()->getConstraints(),
                    fn(Constraint $constraint) => $constraint->constraintType !== 'CHECK'
                )
            )
        );
    }

    public function testRenameColumn(): void
    {
        $this->instance->renameColumn('type', 'kind');

        self::assertEquals(
            'kind',
            $this->instance->reset()->getColumn('kind')->getColumnName()
        );
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
