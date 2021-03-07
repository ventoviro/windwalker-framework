<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Test\Schema\Ddl;

use PHPUnit\Framework\TestCase;
use Windwalker\Database\Schema\Ddl\Column;

/**
 * The ColumnTest class.
 */
class ColumnTest extends TestCase
{
    /**
     * @var Column
     */
    protected $instance;

    /**
     * @see  Column::__construct
     */
    public function testConstructWithoutLength(): void
    {
        $col = new Column(
            'id',
            'integer',
            false,
            1,
            []
        );

        self::assertEquals(
            'integer',
            $col->getDataType()
        );
        self::assertEquals(
            null,
            $col->getNumericPrecision()
        );
        self::assertEquals(
            null,
            $col->getNumericScale()
        );
        self::assertEquals(
            null,
            $col->getCharacterOctetLength()
        );
        self::assertEquals(
            '',
            $col->getLengthExpression()
        );
    }

    public function testConstructVarchar(): void
    {
        $col = new Column(
            'alias',
            'varchar(255)',
            false,
            1,
            []
        );

        self::assertEquals(
            'varchar',
            $col->getDataType()
        );
        self::assertEquals(
            null,
            $col->getNumericPrecision()
        );
        self::assertEquals(
            null,
            $col->getNumericScale()
        );
        self::assertEquals(
            255,
            $col->getCharacterMaximumLength()
        );
        self::assertEquals(
            '255',
            $col->getLengthExpression()
        );
    }

    public function testConstructInteger(): void
    {
        $col = new Column(
            'id',
            'int(11)',
            false,
            1,
            []
        );

        self::assertEquals(
            'int',
            $col->getDataType()
        );
        self::assertEquals(
            11,
            $col->getNumericPrecision()
        );
        self::assertEquals(
            null,
            $col->getNumericScale()
        );
        self::assertEquals(
            '11',
            $col->getLengthExpression()
        );
    }

    public function testConstructDecimal(): void
    {
        $col = new Column(
            'price',
            'decimal(10,6)',
            false,
            1.5,
            []
        );

        self::assertEquals(
            'decimal',
            $col->getDataType()
        );
        self::assertEquals(
            10,
            $col->getNumericPrecision()
        );
        self::assertEquals(
            6,
            $col->getNumericScale()
        );
        self::assertEquals(
            '10,6',
            $col->getLengthExpression()
        );
    }

    /**
     * @see  Column::fill
     */
    public function testBind(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    protected function setUp(): void
    {
        $this->instance = null;
    }

    protected function tearDown(): void
    {
    }
}
