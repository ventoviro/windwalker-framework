<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test\Schema;

use Windwalker\Database\Schema\Schema;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

/**
 * The SchemaTest class.
 */
class SchemaTest extends AbstractDatabaseTestCase
{
    protected ?Schema $instance;

    /**
     * @see  Schema::getNullDate
     */
    public function testGetNullDate(): void
    {
        self::assertEquals(
            $this->instance->getNullDate(),
            self::$db->getNullDate()
        );
    }

    /**
     * @see  Schema::addIndex()
     */
    public function testAddIndex(): void
    {
        $this->instance->addIndex('id');
        $this->instance->addIndex(['foo', 'bar']);

        $keys = $this->instance->getIndexes();

        self::assertEquals(['id'], array_keys($keys['idx_ww_flower_id']->getColumns()));
        self::assertEquals(['foo', 'bar'], array_keys($keys['idx_ww_flower_foo_bar']->getColumns()));
    }

    /**
     * @see  Schema::__call
     */
    public function testMagicCall(): void
    {
        $this->instance->primary('id');
        $this->instance->integer('category_id')->length(11);
        $this->instance->double('pos')->length('20,4');
        $this->instance->decimal('price')->length('20,4');
        $this->instance->varchar('intro')->length(512);

        $cols = [];

        foreach ($this->instance->getColumns() as $column) {
            $cols[] = sprintf('%s %s', $column->getColumnName(), $column->getTypeExpression());
        }

        self::assertStringSafeEquals(
            <<<EOT
            id integer
            category_id integer(11)
            pos double(20,4)
            price decimal(20,4)
            intro varchar(512)
            EOT,
            implode("\n", $cols)
        );
    }

    /**
     * @see  Schema::getDateFormat
     */
    public function testGetDateFormat(): void
    {
        self::assertEquals(
            self::$db->getDateFormat(),
            $this->instance->getDateFormat()
        );
    }

    /**
     * @see  Schema::setColumns
     */
    public function testSetColumns(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Schema::getKeys
     */
    public function testGetIndexes(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Schema::__construct
     */
    public function testConstruct(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Schema::addColumn
     */
    public function testAddColumn(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Schema::getColumns
     */
    public function testGetColumns(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Schema::addPrimaryKey()
     */
    public function testAddPrimaryKey(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Schema::primary
     */
    public function testPrimary(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Schema::addUniqueKey
     */
    public function testAddUniqueKey(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    protected function setUp(): void
    {
        $this->instance = static::$db->getTable('ww_flower')->createSchemaObject();
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
