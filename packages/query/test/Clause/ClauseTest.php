<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Test\Clause;

use PHPUnit\Framework\TestCase;
use Windwalker\Query\Clause\Clause;

use function Windwalker\Query\clause;

/**
 * The ClauseTest class.
 */
class ClauseTest extends TestCase
{
    /**
     * @var Clause
     */
    protected $instance;

    /**
     * @param  string  $name
     * @param  array   $elements
     * @param  string  $glue
     * @param  string  $expected
     *
     * @see          Clause::__toString
     *
     * @dataProvider basicUsageProvider
     */
    public function testBasicUsage(string $name, array $elements, string $glue, string $expected): void
    {
        self::assertEquals(
            $expected,
            (string) new Clause($name, $elements, $glue)
        );
    }

    public function basicUsageProvider(): array
    {
        return [
            [
                'WHERE',
                ['foo > 0'],
                ' AND ',
                'WHERE foo > 0',
            ],
            [
                'WHERE',
                ['foo > 0', "bar = '123'"],
                ' AND ',
                'WHERE foo > 0 AND bar = \'123\'',
            ],
            [
                'IN()',
                [1, 2, 3],
                ', ',
                'IN(1, 2, 3)',
            ],
            [
                '()',
                ['a = b', 'c = d'],
                ' OR ',
                '(a = b OR c = d)',
            ],
        ];
    }

    public function testNested(): void
    {
        $clause = new Clause('WHERE');

        $clause->append(new Clause('', ['foo', '=', "'bar'"]));
        $clause->append(new Clause('OR', ['foo', '<', 5]));
        $clause->append(
            new Clause(
                'AND ()', [
                new Clause('', ['flower', '=', "'sakura'"]),
                new Clause('OR', ['flower', 'IS', 'NULL']),
            ]
            )
        );

        self::assertEquals(
            'WHERE foo = \'bar\' OR foo < 5 AND (flower = \'sakura\' OR flower IS NULL)',
            (string) $clause
        );
    }

    /**
     * @see  Clause::setGlue
     */
    public function testGetSetGlue(): void
    {
        $clause = new Clause('IN()', [], '');
        $clause->setGlue(', ');

        self::assertEquals(', ', $clause->getGlue());
    }

    /**
     * @see  Clause::setName
     */
    public function testSetName(): void
    {
        $clause = new Clause('');
        $clause->setName('HELLO');

        self::assertEquals('HELLO', $clause->getName());
    }

    /**
     * @see  Clause::getElements
     */
    public function testGetSetElements(): void
    {
        $clause = new Clause('IN()', [], '');
        $clause->setElements([1, 2, 3]);

        self::assertEquals([1, 2, 3], $clause->getElements());
    }

    /**
     * @see  Clause::append
     */
    public function testAppend(): void
    {
        $clause = new Clause('IN()', [], '');
        $clause->append(1);
        $clause->append(2);
        $clause->append(3);

        self::assertEquals([1, 2, 3], $clause->getElements());
    }

    /**
     * @see  Clause::__clone
     */
    public function testClone(): void
    {
        $clause = new Clause('IN()', [new Clause()], '');
        $clause2 = clone $clause;

        self::assertNotSame(
            $clause->getElements()[0],
            $clause2->getElements()[0]
        );
    }

    public function testMagic(): void
    {
        $clause = clause('hello', [1, 2, 3]);

        self::assertEquals('hello', $clause->name);
        self::assertEquals([1, 2, 3], $clause->elements);
        self::assertEquals(' ', $clause->glue);

        self::assertTrue(isset($clause->elements));
        self::assertCount(3, $clause);
    }

    protected function setUp(): void
    {
        $this->instance = null;
    }

    protected function tearDown(): void
    {
    }
}
