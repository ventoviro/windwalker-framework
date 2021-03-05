<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Query\Bounded\BoundedHelper;
use Windwalker\Query\Clause\JoinClause;
use Windwalker\Query\Grammar\AbstractGrammar;
use Windwalker\Query\Grammar\BaseGrammar;
use Windwalker\Query\Query;
use Windwalker\Query\Test\Mock\MockEscaper;
use Windwalker\Test\Traits\QueryTestTrait;
use Windwalker\Utilities\Reflection\ReflectAccessor;

use function Windwalker\Query\clause;
use function Windwalker\Query\expr;
use function Windwalker\Query\qn;
use function Windwalker\Query\val;
use function Windwalker\raw;

/**
 * The QueryTest class.
 */
class QueryTest extends TestCase
{
    use QueryTestTrait;

    /**
     * @var Query
     */
    protected Query $instance;

    protected static mixed $escaper;

    /**
     * @param  array        $args
     * @param  array        $addArgs
     * @param  string       $expected
     * @param  string|null  $subQueryAlias
     * @param  string|null  $modifiedSql
     *
     * @see          Query::select
     *
     * @dataProvider selectProvider
     */
    public function testSelect(
        array $args,
        ?array $addArgs,
        string $expected,
        ?string $subQueryAlias = null,
        ?string $modifiedSql = null
    ): void {
        $q = $this->instance->select(...$args);

        if ($addArgs !== null) {
            $q = $q->selectAs(...$addArgs);
        }

        self::assertSqlEquals($expected, (string) $q);

        if ($subQueryAlias) {
            $sub = $q->getSubQuery($subQueryAlias);

            self::assertInstanceOf(Query::class, $sub);

            $sub->select('newcol');

            self::assertSqlEquals($modifiedSql, (string) $q);
        }
    }

    public function selectProvider(): array
    {
        return [
            'array and args' => [
                // args
                [['a', 'b'], 'c', 'd'],
                null,
                // expected
                'SELECT "a", "b", "c", "d"',
            ],
            'AS alias' => [
                // args
                [['a AS aaa', 'b'], 'c AS ccc', 'd'],
                null,
                // expected
                'SELECT "a" AS "aaa", "b", "c" AS "ccc", "d"',
            ],
            'dots' => [
                // args
                [['a.aa AS aaa', 'b.bb'], 'c AS ccc', 'd.dd'],
                null,
                // expected
                'SELECT "a"."aa" AS "aaa", "b"."bb", "c" AS "ccc", "d"."dd"',
            ],
            'raw and clause' => [
                // args
                [[raw('COUNT(*) AS a')], expr('DISTINCT', qn('foo AS bar')), 'c AS ccc'],
                null,
                // expected
                'SELECT COUNT(*) AS a, DISTINCT "foo" AS "bar", "c" AS "ccc"',
            ],
            'selectAs' => [
                // args
                ['b AS bbb'],
                [raw('COUNT(*)'), 'count'],
                // expected
                'SELECT "b" AS "bbb", COUNT(*) AS "count"',
            ],
            'raw and selectAs with clause' => [
                // args
                [[raw('COUNT(*) AS a')], 'c AS ccc'],
                [expr('DISTINCT', qn('foo AS bar'))],
                // expected
                'SELECT COUNT(*) AS a, "c" AS "ccc", DISTINCT "foo" AS "bar"',
            ],
            'sub query with Closure' => [
                // args
                [
                    static function (Query $query) {
                        $query->select('*')
                            ->from('foo')
                            ->alias('foooo');
                    },
                    'bar AS barrr',
                ],
                null,
                // expected
                'SELECT (SELECT * FROM "foo") AS "foooo", "bar" AS "barrr"',
            ],
            // TODO: Move to new test
            'sub query modified' => [
                // args
                [
                    self::createQuery()
                        ->select('*')
                        ->from('foo')
                        ->alias('foooo'),
                    'bar AS barrr',
                ],
                null,
                // expected
                'SELECT (SELECT * FROM "foo") AS "foooo", "bar" AS "barrr"',
                // Sub query
                'foooo',
                'SELECT (SELECT *, "newcol" FROM "foo") AS "foooo", "bar" AS "barrr"',
            ],
        ];
    }

    public function testSelectWithNoColumns()
    {
        $this->instance->from('flowers')
            ->where('id', 123);

        self::assertSqlEquals(
            'SELECT * FROM "flowers" WHERE "id" = 123',
            $this->instance->render(true)
        );
    }

    /**
     * @param  mixed        $tables
     * @param  string|null  $alias
     * @param  string       $expected
     *
     * @see          Query::from
     *
     * @dataProvider fromProvider
     */
    public function testFrom($tables, ?string $alias, string $expected): void
    {
        $q = $this->instance
            ->select('*')
            ->from($tables, $alias);

        self::assertSqlEquals($expected, (string) $q);
    }

    public function fromProvider(): array
    {
        return [
            'Simple from' => [
                'foo',
                null,
                'SELECT * FROM "foo"',
            ],
            'Simple from as' => [
                'a.foo',
                'foo',
                'SELECT * FROM "a"."foo" AS "foo"',
            ],
            'Multiple tables' => [
                [
                    ['foo', 'f'],
                    ['bar', 'b'],
                    ['yoo', 'y']
                ],
                'nouse',
                'SELECT * FROM "foo" AS "f", "bar" AS "b", "yoo" AS "y"',
            ],
            'single sub query' => [
                self::createQuery()
                    ->select('*')
                    ->from('flower'),
                null,
                'SELECT * FROM (SELECT * FROM "flower")',
            ],
            'single sub query as' => [
                self::createQuery()
                    ->select('*')
                    ->from('flower'),
                'f',
                'SELECT * FROM (SELECT * FROM "flower") AS "f"',
            ],
            'single sub query with self-alias' => [
                self::createQuery()
                    ->select('*')
                    ->from('flower')
                    ->alias('fl'),
                null,
                'SELECT * FROM (SELECT * FROM "flower") AS "fl"',
            ],
            'single sub query with self-alias and as' => [
                self::createQuery()
                    ->select('*')
                    ->from('flower')
                    ->alias('fl'),
                'f',
                'SELECT * FROM (SELECT * FROM "flower") AS "f"',
            ],
            'Multiple tables with sub query' => [
                [
                    ['ace', 'a'],
                    [
                        self::createQuery()
                            ->select('*')
                            ->from('flower')
                            ->alias('fl_nouse'),
                        'f'
                    ]
                ],
                'nouse',
                'SELECT * FROM "ace" AS "a", (SELECT * FROM "flower") AS "f"',
            ],
            'Multiple tables with sub query closure' => [
                [
                    ['ace', 'a'],
                    [
                        static function (Query $q) {
                            $q->select('*')
                                ->from('flower')
                                ->alias('fl_nouse');
                        },
                        'f'
                    ]
                ],
                'nouse',
                'SELECT * FROM "ace" AS "a", (SELECT * FROM "flower") AS "f"',
            ],
        ];
    }

    /**
     * testJoin
     *
     * @param  string  $expt
     * @param  mixed   ...$joins
     *
     * @return  void
     *
     * @dataProvider  joinProvider
     */
    public function testJoin(string $expt, ...$joins)
    {
        $q = self::createQuery()
            ->select('*')
            ->from('foos', 'foo');

        foreach ($joins as $join) {
            $q->join(...$join);
        }

        self::assertSqlEquals(
            'SELECT * FROM "foos" AS "foo" ' . $expt,
            $q->render(true)
        );
    }

    public function joinProvider(): array
    {
        return [
            'Simple left join' => [
                'LEFT JOIN "bars" AS "bar" ON "bar"."id" = "foo"."bar_id"',
                [
                    'LEFT',
                    'bars',
                    'bar',
                    'bar.id',
                    '=',
                    'foo.bar_id',
                ],
            ],
            'Join with simple on' => [
                'LEFT JOIN "bars" AS "bar" ON "bar"."id" = "foo"."bar_id"',
                [
                    'LEFT',
                    'bars',
                    'bar',
                    'bar.id',
                    'foo.bar_id',
                ],
            ],
            'Join with multiple on' => [
                'LEFT JOIN "bars" AS "bar" ON "bar"."id" = "foo"."bar_id" AND "bar"."type" = "foo"."type"',
                [
                    'LEFT',
                    'bars',
                    'bar',
                    'bar.id',
                    '=',
                    'foo.bar_id',
                    'bar.type',
                    '=',
                    'foo.type',
                ],
            ],
            'Join with multiple on array' => [
                'LEFT JOIN "bars" AS "bar" ON "bar"."id" = "foo"."bar_id" AND "bar"."flower" IN (\'rose\', \'sakura\')',
                [
                    'LEFT',
                    'bars',
                    'bar',
                    'bar.id',
                    '=',
                    'foo.bar_id',
                    'bar.flower',
                    '=',
                    ['rose', 'sakura'],
                ],
            ],
            'Join with value' => [
                'LEFT JOIN "bars" AS "bar" ON "bar"."id" = "foo"."bar_id" AND "bar"."flower" = \'rose\'',
                [
                    'LEFT',
                    'bars',
                    'bar',
                    'bar.id',
                    '=',
                    'foo.bar_id',
                    'bar.flower',
                    '=',
                    val('rose'),
                ],
            ],
            'Join with callback on' => [
                'LEFT JOIN "bars" AS "bar" ON "bar"."id" = "foo"."bar_id" AND "bar"."type" = "foo"."type"',
                [
                    'LEFT',
                    'bars',
                    'bar',
                    static function (JoinClause $join) {
                        $join->on('bar.id', 'foo.bar_id');
                        $join->on('bar.type', 'foo.type');
                    },
                ],
            ],
            'Join with callback onRaw' => [
                'LEFT JOIN "bars" AS "bar" ON "bar"."id" = "foo"."bar_id" AND "bar"."flower" = \'sakura\'',
                [
                    'LEFT',
                    'bars',
                    'bar',
                    static function (JoinClause $join) {
                        $join->on('bar.id', 'foo.bar_id');
                        $join->onRaw('%n = %q', 'bar.flower', 'sakura');
                    },
                ],
            ],
            'Join with callback nested on' => [
                'LEFT JOIN "bars" AS "bar" ON ("a" = "b" OR "c" = "d")',
                [
                    'LEFT',
                    'bars',
                    'bar',
                    static function (JoinClause $join) {
                        $join->on(
                            static function (JoinClause $join) {
                                $join->on('a', 'b');
                                $join->on('c', 'd');
                            },
                            'OR'
                        );
                    },
                ],
            ],
            'Join with callback or on' => [
                'LEFT JOIN "bars" AS "bar" ON "bar"."id" = "foo"."bar_id" AND ("a" = "b" OR "c" = "d")',
                [
                    'LEFT',
                    'bars',
                    'bar',
                    static function (JoinClause $join) {
                        $join->on('bar.id', 'foo.bar_id');
                        $join->orOn(static function (JoinClause $join) {
                            $join->on('a', 'b');
                            $join->on('c', 'd');
                        });
                    },
                ],
            ],
            'Multiple join' => [
                'LEFT JOIN "bars" AS "bar" ON "foo"."bar_id" = "bar"."id" RIGHT JOIN "flowers" AS "fl" ON "fl"."bar_id" = "bar"."id"',
                [
                    'LEFT',
                    'bars',
                    'bar',
                    'foo.bar_id',
                    'bar.id'
                ],
                [
                    'RIGHT',
                    'flowers',
                    'fl',
                    'fl.bar_id',
                    'bar.id'
                ],
            ],
            'Join sub query' => [
                'LEFT JOIN (SELECT COUNT(*) AS "count", "id" FROM "bar" GROUP BY "bar"."id") AS "bar" ON "foo"."bar_id" = "bar"."id"',
                [
                    'LEFT',
                    self::createQuery()
                        ->selectAs(raw('COUNT(*)'), 'count')
                        ->select('id')
                        ->from('bar')
                        ->group('bar.id'),
                    'bar',
                    'foo.bar_id',
                    'bar.id'
                ]
            ],
            'Join sub query callback' => [
                'LEFT JOIN (SELECT COUNT(*) AS "count", "id" FROM "bar" GROUP BY "bar"."id") AS "bar" ON "foo"."bar_id" = "bar"."id"',
                [
                    'LEFT',
                    function (Query $query) {
                        $query->selectRaw('COUNT(*) AS %n', 'count')
                            ->select('id')
                            ->from('bar')
                            ->group('bar.id');
                    },
                    'bar',
                    'foo.bar_id',
                    'bar.id'
                ]
            ],
        ];
    }

    public function testUnion(): void
    {
        // Select and union
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->where('id', '>', 12)
            ->group('user_id');

        $q->union(
            self::createQuery()
                ->select('*')
                ->from('bar')
                ->where('id', '<', 50)
                ->alias('bar')
        );

        self::assertSqlEquals(
            'SELECT * FROM "foo" WHERE "id" > 12 GROUP BY "user_id" UNION (SELECT * FROM "bar" WHERE "id" < 50)',
            $q->render(true)
        );

        // Union wrap every select
        $q = self::createQuery();

        $q->union(
            self::createQuery()
                ->select('*')
                ->from('foo')
                ->where('id', '>', 12)
        );

        $q->union(
            self::createQuery()
                ->select('*')
                ->from('bar')
                ->where('id', '<', 50)
        );

        // Group will be ignore
        $q->group('id')
            ->order('id', 'DESC');

        self::assertSqlEquals(
            '(SELECT * FROM "foo" WHERE "id" > 12) UNION (SELECT * FROM "bar" WHERE "id" < 50) ORDER BY "id" DESC',
            $q
        );
    }

    public function testInsert(): void
    {
        $this->instance->insert('foo', true)
            ->columns('id', 'title', ['foo', 'bar'], 'yoo')
            ->values(
                [1, 'A', 'a', null, raw('CURRENT_TIMESTAMP()')],
                [2, 'B', 'b', null, raw('CURRENT_TIMESTAMP()')],
                [3, 'C', 'c', null, raw('CURRENT_TIMESTAMP()')]
            );

        self::assertSqlEquals(
            <<<SQL
INSERT INTO "foo"
("id", "title", "foo", "bar", "yoo")
VALUES
    (1, 'A', 'a', NULL, CURRENT_TIMESTAMP()),
    (2, 'B', 'b', NULL, CURRENT_TIMESTAMP()),
    (3, 'C', 'c', NULL, CURRENT_TIMESTAMP())
SQL
            ,
            $this->instance
        );

        $q = self::createQuery()
            ->insert('foo')
            ->set('id', 1)
            ->set([
                'title' => 'A',
                'foo' => 'a',
                'bar' => null,
                'yoo' => raw('CURRENT_TIMESTAMP()')
            ]);

        self::assertSqlEquals(
            <<<SQL
INSERT INTO "foo" SET "id" = 1, "title" = 'A', "foo" = 'a', "bar" = NULL, "yoo" = CURRENT_TIMESTAMP()
SQL
            ,
            $q
        );

        $q = self::createQuery()
            ->insert('foo')
            ->columns('id', 'title')
            ->values(
                self::createQuery()
                    ->select('id', 'title')
                    ->from('articles'),
                self::createQuery()
                    ->select('id', 'title')
                    ->from('categories')
            );

        self::assertSqlEquals(
            <<<SQL
INSERT INTO "foo" ("id", "title")
(SELECT "id", "title" FROM "articles") UNION (SELECT "id", "title" FROM "categories")
SQL
            ,
            $q
        );
    }

    public function testUpdate(): void
    {
        $q = self::createQuery()
            ->update('foo')
            ->set('a', 'b')
            ->set('c', 5)
            ->set([
                'foo' => 'bar',
                'yoo' => 'goo'
            ])
            ->where('id', 123);

        self::assertSqlEquals(
            'UPDATE "foo" SET "a" = \'b\', "c" = 5, "foo" = \'bar\', "yoo" = \'goo\' WHERE "id" = 123',
            $q
        );

        $q = self::createQuery()
            ->update('foo', 'f')
            ->leftJoin('hoo', 'h', 'h.foo_id', 'foo.id')
            ->set(
                'a',
                self::createQuery()
                    ->select('*')
                    ->from('yoo')
                    ->where('id', 1)
            )
            ->set([
                'f.col' => $q->raw('%n + 1', 'b.col')
            ])
            ->where('id', 123);

        self::assertSqlEquals(
            <<<'SQL'
UPDATE "foo" AS "f"
    LEFT JOIN "hoo" AS "h" ON "h"."foo_id" = "foo"."id"
SET "a" = (SELECT * FROM "yoo" WHERE "id" = 1),
    "f"."col" = "b"."col" + 1
WHERE "id" = 123
SQL
            ,
            $q
        );
    }

    public function testDelete()
    {
        $q = self::createQuery()
            ->delete('foo')
            ->where('id', 123);

        self::assertSqlEquals(
            'DELETE FROM "foo" WHERE "id" = 123',
            $q
        );

        $q = self::createQuery()
            ->delete('foo', 'f')
            ->leftJoin('hoo', 'h', 'h.foo_id', 'foo.id')
            ->where('h.created', '<', '2020-03-02');

        self::assertSqlEquals(
            <<<'SQL'
DELETE FROM "foo" AS "f"
LEFT JOIN "hoo" AS "h" ON "h"."foo_id" = "foo"."id"
WHERE "h"."created" < '2020-03-02'
SQL
            ,
            $q
        );
    }

    /**
     * testAs
     *
     * @param  string  $expt
     * @param  mixed   $value
     * @param  mixed   $alias
     * @param  bool    $isColumn
     *
     * @return  void
     *
     * @dataProvider asProvider
     */
    public function testAs(string $expt, $value, $alias, bool $isColumn = true): void
    {
        self::assertEquals(static::replaceQn($expt), (string) $this->instance->as($value, $alias, $isColumn));
    }

    public function asProvider(): array
    {
        return [
            'Simple quote name' => [
                '"foo"',
                'foo',
                null,
                true,
            ],
            'Column with as' => [
                '"foo" AS "f"',
                'foo',
                'f',
                true,
            ],
            'String value' => [
                '\'foo\'',
                'foo',
                false,
                false,
            ],
            'Sub query with as' => [
                '(SELECT * FROM "bar") AS "bar"',
                self::createQuery()
                    ->select('*')
                    ->from('bar'),
                'bar',
                true,
            ],
            'Sub query contains as but override' => [
                '(SELECT * FROM "bar") AS "bar"',
                self::createQuery()
                    ->select('*')
                    ->from('bar')
                    ->alias('b'),
                'bar',
                true,
            ],
            'Sub query contains alias' => [
                '(SELECT * FROM "bar") AS "b"',
                self::createQuery()
                    ->select('*')
                    ->from('bar')
                    ->alias('b'),
                null,
                true,
            ],
            'Sub query contains alias but force ignore' => [
                '(SELECT * FROM "bar")',
                self::createQuery()
                    ->select('*')
                    ->from('bar')
                    ->alias('b'),
                false,
                true,
            ],
            'Sub query as value' => [
                '(SELECT * FROM "bar")',
                self::createQuery()
                    ->select('*')
                    ->from('bar'),
                false,
                false,
            ],
        ];
    }

    /**
     * testWhere
     *
     * @param  string  $expt
     * @param  array   $wheres
     *
     * @return  void
     *
     * @dataProvider whereProvider
     */
    public function testWhere(string $expt, ...$wheres)
    {
        $this->instance->select('*')
            ->from('a');

        foreach ($wheres as $whereArgs) {
            $this->instance->where(...$whereArgs);
        }

        // Test self merged bounded
        self::assertSqlEquals(
            $expt,
            BoundedHelper::emulatePrepared(
                $this->instance,
                (string) $this->instance->render(false, $bounded),
                $bounded
            )
        );

        // Test double bounded should get same sequence
        self::assertSqlEquals(
            $expt,
            BoundedHelper::emulatePrepared(
                $this->instance,
                (string) $this->instance->render(),
                $this->instance->getMergedBounded()
            )
        );

        // Test emulate prepared
        self::assertSqlEquals(
            $expt,
            $this->instance->render(true)
        );
    }

    public function whereProvider(): array
    {
        return [
            'Simple where =' => [
                'SELECT * FROM "a" WHERE "foo" = \'bar\'',
                ['foo', 'bar'],
            ],
            'Where <' => [
                'SELECT * FROM "a" WHERE "foo" < \'bar\'',
                ['foo', '<', 'bar'],
            ],
            'Where chain' => [
                'SELECT * FROM "a" WHERE "foo" < 123 AND "baz" = \'bax\' AND "yoo" != \'goo\'',
                ['foo', '<', 123],
                ['baz', '=', 'bax'],
                ['yoo', '!=', 'goo'],
            ],
            'Where null' => [
                'SELECT * FROM "a" WHERE "foo" IS NULL',
                ['foo', null],
            ],
            'Where is null' => [
                'SELECT * FROM "a" WHERE "foo" IS NULL',
                ['foo', 'IS', null],
            ],
            'Where is not null' => [
                'SELECT * FROM "a" WHERE "foo" IS NOT NULL',
                ['foo', 'IS NOT', null],
            ],
            'Where = null' => [
                'SELECT * FROM "a" WHERE "foo" IS NULL',
                ['foo', '=', null],
            ],
            'Where != null' => [
                'SELECT * FROM "a" WHERE "foo" IS NOT NULL',
                ['foo', '!=', null],
            ],
            'Where in' => [
                'SELECT * FROM "a" WHERE "foo" IN (1, 2, \'yoo\')',
                ['foo', 'in', [1, 2, 'yoo']],
            ],
            'Where between' => [
                'SELECT * FROM "a" WHERE "foo" BETWEEN 1 AND 100',
                ['foo', 'between', [1, 100]],
            ],
            'Where between with column name' => [
                'SELECT * FROM "a" WHERE "foo" BETWEEN "a"."lft" AND "b"."rgt"',
                ['foo', 'between', [qn('a.lft'), qn('b.rgt')]],
            ],
            'Where not between' => [
                'SELECT * FROM "a" WHERE "foo" NOT BETWEEN 1 AND 100',
                ['foo', 'not between', [1, 100]],
            ],
            // Bind with name
            // 'Where bind with var name' => [
            //     'SELECT * FROM "a" WHERE "foo" = \'Hello\'',
            //     ['foo', '=', ':foo', 'Hello']
            // ],
            // Where array and nested
            'Where array' => [
                'SELECT * FROM "a" WHERE "foo" = \'bar\' AND "yoo" = \'hello\' AND "flower" IN (SELECT "id" FROM "flower" WHERE "id" = 5)',
                [
                    // arg 1 is array
                    [
                        ['foo', 'bar'],
                        ['yoo', '=', 'hello'],
                        [
                            'flower',
                            'in',
                            self::createQuery()
                                ->select('id')
                                ->from('flower')
                                ->where('id', 5),
                        ],
                    ],
                ],
            ],
            'Where array with more conditions' => [
                'SELECT * FROM "a" WHERE foo >= \'bar\' AND "yoo" = \'hello\' AND "flower" IN (\'a\', \'b\', \'c\')',
                [
                    // arg 1 is array
                    [
                        'foo >= \'bar\'',
                        'yoo' => 'hello',
                        'flower' => [
                            'a',
                            'b',
                            'c'
                        ]
                    ],
                ],
            ],
            'Where nested' => [
                'SELECT * FROM "a" WHERE "foo" = \'bar\' AND ("yoo" = \'goo\' AND "flower" != \'Sakura\')',
                ['foo', 'bar'],
                [
                    static function (Query $query) {
                        $query->where('yoo', 'goo')
                            ->where('flower', '!=', 'Sakura');
                    },
                ],
            ],

            'Where nested or' => [
                'SELECT * FROM "a" WHERE "foo" = \'bar\' AND ("yoo" = \'goo\' OR "flower" != \'Sakura\')',
                ['foo', 'bar'],
                [
                    static function (Query $query) {
                        $query->where('yoo', 'goo')
                            ->where('flower', '!=', 'Sakura');
                    },
                    'or',
                ],
            ],

            // Sub query
            'Where not exists sub query' => [
                'SELECT * FROM "a" WHERE "foo" NOT EXISTS (SELECT "id" FROM "flower" WHERE "id" = 5)',
                [
                    'foo',
                    'not exists',
                    self::createQuery()
                        ->select('id')
                        ->from('flower')
                        ->where('id', 5),
                ],
            ],
            'Where not exists sub query cllback' => [
                'SELECT * FROM "a" WHERE "foo" NOT EXISTS (SELECT "id" FROM "flower" WHERE "id" = 5)',
                [
                    'foo',
                    'not exists',
                    static function (Query $q) {
                        $q->select('id')
                            ->from('flower')
                            ->where('id', 5);
                    },
                ],
            ],
            'Where sub query equals value' => [
                'SELECT * FROM "a" WHERE (SELECT "id" FROM "flower" WHERE "id" = 5) = 123',
                [
                    self::createQuery()
                        ->select('id')
                        ->from('flower')
                        ->where('id', 5),
                    '=',
                    123,
                ],
            ],

            // Where with raw wrapper
            'Where with raw wrapper' => [
                'SELECT * FROM "a" WHERE foo = YEAR(date)',
                [raw('foo'), raw('YEAR(date)')],
            ],
        ];
    }

    public function testOrWhere()
    {
        // Array
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->where('foo', 'bar')
            ->orWhere(
                [
                    ['yoo', 'goo'],
                    ['flower', '!=', 'Sakura'],
                    ['hello', [1, 2, 3]],
                ]
            );

        self::assertSqlEquals(
            'SELECT * FROM "foo" WHERE "foo" = \'bar\' AND ("yoo" = \'goo\' OR "flower" != \'Sakura\' OR "hello" IN (1, 2, 3))',
            $q->render(true)
        );

        // Closure
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->where('foo', 'bar')
            ->orWhere(
                function (Query $query) {
                    $query->where('yoo', 'goo');
                    $query->where('flower', '!=', 'Sakura');
                    $query->where('hello', [1, 2, 3]);
                }
            );

        self::assertSqlEquals(
            'SELECT * FROM "foo" WHERE "foo" = \'bar\' AND ("yoo" = \'goo\' OR "flower" != \'Sakura\' OR "hello" IN (1, 2, 3))',
            $q->render(true)
        );

        // Nested
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->where('foo', 'bar')
            ->orWhere(
                function (Query $query) {
                    $query->where('yoo', 'goo');
                    $query->where('flower', '!=', 'Sakura');
                    $query->where(
                        function (Query $query) {
                            $query->where('hello', [1, 2, 3]);
                            $query->where('id', '<', 999);
                        }
                    );
                }
            );

        self::assertSqlFormatEquals(
            <<<SQL
SELECT * FROM "foo" WHERE "foo" = 'bar'
AND (
    "yoo" = 'goo'
    OR "flower" != 'Sakura'
    OR ("hello" IN (1, 2, 3) AND "id" < 999)
)
SQL
            ,
            $q->render(true)
        );
    }

    public function testWhereVariant()
    {
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->whereIn('id', [1, 2, 3])
            ->whereBetween('time', '2012-03-30', '2020-02-24')
            ->whereNotIn('created', [55, 66])
            ->whereNotLike('content', '%qwe%');

        self::assertSqlEquals(
            'SELECT * FROM "foo" WHERE "id" IN (1, 2, 3) AND "time" BETWEEN \'2012-03-30\' AND \'2020-02-24\' '
                . 'AND "created" NOT IN (55, 66) AND "content" NOT LIKE \'%qwe%\'',
            $q->render(true)
        );


        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->havingIn('id', [1, 2, 3])
            ->havingBetween('time', '2012-03-30', '2020-02-24')
            ->havingNotIn('created', [55, 66])
            ->havingNotLike('content', '%qwe%');

        self::assertSqlEquals(
            'SELECT * FROM "foo" HAVING "id" IN (1, 2, 3) AND "time" BETWEEN \'2012-03-30\' AND \'2020-02-24\' '
                . 'AND "created" NOT IN (55, 66) AND "content" NOT LIKE \'%qwe%\'',
            $q->render(true)
        );
    }

    /**
     * testWhere
     *
     * @param  string  $expt
     * @param  array   $wheres
     *
     * @return  void
     *
     * @dataProvider havingProvider
     */
    public function testHaving(string $expt, ...$wheres)
    {
        $this->instance->select('*')
            ->from('a');

        foreach ($wheres as $whereArgs) {
            $this->instance->having(...$whereArgs);
        }

        // Test self merged bounded
        self::assertSqlEquals(
            $expt,
            BoundedHelper::emulatePrepared(
                $this->instance,
                (string) $this->instance->render(false, $bounded),
                $bounded
            )
        );

        // Test double bounded should get same sequence
        self::assertSqlEquals(
            $expt,
            BoundedHelper::emulatePrepared(
                $this->instance,
                (string) $this->instance->render(),
                $this->instance->getMergedBounded()
            )
        );

        // Test emulate prepared
        self::assertSqlEquals(
            $expt,
            $this->instance->render(true)
        );
    }

    public function havingProvider(): array
    {
        return [
            'Simple having =' => [
                'SELECT * FROM "a" HAVING "foo" = \'bar\'',
                ['foo', 'bar'],
            ],
            'Having <' => [
                'SELECT * FROM "a" HAVING "foo" < \'bar\'',
                ['foo', '<', 'bar'],
            ],
            'Having chain' => [
                'SELECT * FROM "a" HAVING "foo" < 123 AND "baz" = \'bax\' AND "yoo" != \'goo\'',
                ['foo', '<', 123],
                ['baz', '=', 'bax'],
                ['yoo', '!=', 'goo'],
            ],
            'Having null' => [
                'SELECT * FROM "a" HAVING "foo" IS NULL',
                ['foo', null],
            ],
            'Having is null' => [
                'SELECT * FROM "a" HAVING "foo" IS NULL',
                ['foo', 'IS', null],
            ],
            'Having is not null' => [
                'SELECT * FROM "a" HAVING "foo" IS NOT NULL',
                ['foo', 'IS NOT', null],
            ],
            'Having = null' => [
                'SELECT * FROM "a" HAVING "foo" IS NULL',
                ['foo', '=', null],
            ],
            'Having != null' => [
                'SELECT * FROM "a" HAVING "foo" IS NOT NULL',
                ['foo', '!=', null],
            ],
            'Having in' => [
                'SELECT * FROM "a" HAVING "foo" IN (1, 2, \'yoo\')',
                ['foo', 'in', [1, 2, 'yoo']],
            ],
            'Having between' => [
                'SELECT * FROM "a" HAVING "foo" BETWEEN 1 AND 100',
                ['foo', 'between', [1, 100]],
            ],
            'Having not between' => [
                'SELECT * FROM "a" HAVING "foo" NOT BETWEEN 1 AND 100',
                ['foo', 'not between', [1, 100]],
            ],
            // Bind with name
            // 'Having bind with var name' => [
            //     'SELECT * FROM "a" HAVING "foo" = \'Hello\'',
            //     ['foo', '=', ':foo', 'Hello']
            // ],
            // Having array and nested
            'Having array' => [
                'SELECT * FROM "a" HAVING "foo" = \'bar\' AND "yoo" = \'hello\' AND "flower" IN (SELECT "id" FROM "flower" HAVING "id" = 5)',
                [
                    // arg 1 is array
                    [
                        ['foo', 'bar'],
                        ['yoo', '=', 'hello'],
                        [
                            'flower',
                            'in',
                            self::createQuery()
                                ->select('id')
                                ->from('flower')
                                ->having('id', 5),
                        ],
                    ],
                ],
            ],
            'Having nested' => [
                'SELECT * FROM "a" HAVING "foo" = \'bar\' AND ("yoo" = \'goo\' AND "flower" != \'Sakura\')',
                ['foo', 'bar'],
                [
                    static function (Query $query) {
                        $query->having('yoo', 'goo')
                            ->having('flower', '!=', 'Sakura');
                    },
                ],
            ],

            'Having nested or' => [
                'SELECT * FROM "a" HAVING "foo" = \'bar\' AND ("yoo" = \'goo\' OR "flower" != \'Sakura\')',
                ['foo', 'bar'],
                [
                    static function (Query $query) {
                        $query->having('yoo', 'goo')
                            ->having('flower', '!=', 'Sakura');
                    },
                    'or',
                ],
            ],

            // Sub query
            'Having not exists sub query' => [
                'SELECT * FROM "a" HAVING "foo" NOT EXISTS (SELECT "id" FROM "flower" HAVING "id" = 5)',
                [
                    'foo',
                    'not exists',
                    self::createQuery()
                        ->select('id')
                        ->from('flower')
                        ->having('id', 5),
                ],
            ],
            'Having not exists sub query callback' => [
                'SELECT * FROM "a" HAVING "foo" NOT EXISTS (SELECT "id" FROM "flower" HAVING "id" = 5)',
                [
                    'foo',
                    'not exists',
                    static function (Query $q) {
                        $q->select('id')
                            ->from('flower')
                            ->having('id', 5);
                    },
                ],
            ],
            'Having sub query equals value' => [
                'SELECT * FROM "a" HAVING (SELECT "id" FROM "flower" HAVING "id" = 5) = 123',
                [
                    self::createQuery()
                        ->select('id')
                        ->from('flower')
                        ->having('id', 5),
                    '=',
                    123,
                ],
            ],

            // Having with raw wrapper
            'Having with raw wrapper' => [
                'SELECT * FROM "a" HAVING foo = YEAR(date)',
                [raw('foo'), raw('YEAR(date)')],
            ],
        ];
    }

    public function testOrHaving()
    {
        // Array
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->having('foo', 'bar')
            ->orHaving(
                [
                    ['yoo', 'goo'],
                    ['flower', '!=', 'Sakura'],
                    ['hello', [1, 2, 3]],
                ]
            );

        self::assertSqlEquals(
            'SELECT * FROM "foo" HAVING "foo" = \'bar\' AND ("yoo" = \'goo\' OR "flower" != \'Sakura\' OR "hello" IN (1, 2, 3))',
            $q->render(true)
        );

        // Closure
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->having('foo', 'bar')
            ->orHaving(
                function (Query $query) {
                    $query->having('yoo', 'goo');
                    $query->having('flower', '!=', 'Sakura');
                    $query->having('hello', [1, 2, 3]);
                }
            );

        self::assertSqlEquals(
            'SELECT * FROM "foo" HAVING "foo" = \'bar\' AND ("yoo" = \'goo\' OR "flower" != \'Sakura\' OR "hello" IN (1, 2, 3))',
            $q->render(true)
        );

        // Nested
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->having('foo', 'bar')
            ->orHaving(
                function (Query $query) {
                    $query->having('yoo', 'goo');
                    $query->having('flower', '!=', 'Sakura');
                    $query->having(
                        function (Query $query) {
                            $query->having('hello', [1, 2, 3]);
                            $query->having('id', '<', 999);
                        }
                    );
                }
            );

        self::assertSqlFormatEquals(
            <<<SQL
SELECT * FROM "foo" HAVING "foo" = 'bar'
AND (
    "yoo" = 'goo'
    OR "flower" != 'Sakura'
    OR ("hello" IN (1, 2, 3) AND "id" < 999)
)
SQL
            ,
            $q->render(true)
        );
    }

    public function testOrder()
    {
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->order(
                [
                    ['id', 'ASC'],
                    'f1',
                    ['f2', 'DESC'],
                    'f3',
                ]
            )
            ->order('f4', 'DESC')
            ->order(raw('COUNT(f5)'));

        self::assertSqlEquals(
            'SELECT * FROM "foo" ORDER BY "id" ASC, "f1", "f2" DESC, "f3", "f4" DESC, COUNT(f5)',
            $q->render()
        );
    }

    public function testGroup()
    {
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->order('id')
            ->group('id', ['f1', 'f2'], 'f3')
            ->group('f4')
            ->group(raw('COUNT(f5)'));

        self::assertSqlEquals(
            'SELECT * FROM "foo" GROUP BY "id", "f1", "f2", "f3", "f4", COUNT(f5) ORDER BY "id"',
            $q->render()
        );
    }

    public function testLimitOffset()
    {
        // Limit
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->order('id')
            ->limit(5);

        self::assertSqlEquals(
            'SELECT * FROM "foo" ORDER BY "id" LIMIT 5',
            $q->render()
        );

        // Offset
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->order('id')
            ->offset(10);

        // Only offset will not work
        self::assertSqlEquals(
            'SELECT * FROM "foo" ORDER BY "id"',
            $q->render()
        );

        // Limit & Offset
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->order('id')
            ->limit(5)
            ->offset(15);

        self::assertSqlEquals(
            'SELECT * FROM "foo" ORDER BY "id" LIMIT 15, 5',
            $q->render()
        );
    }

    public function testSubQueryBounded(): void
    {
        $q = self::createQuery()
            ->select('a.*')
            ->from('foo', 'a')
            ->leftJoin(
                self::createQuery()
                    ->select('*')
                    ->from('bar')
                    ->where('id', 'in', [1, 2, 3]),
                'b',
                'b.foo_id',
                '=',
                'a.id'
            )
            ->where('a.id', 123);

        self::assertSqlEquals(
            <<<SQL
SELECT "a".* FROM "foo" AS "a"
    LEFT JOIN
    (SELECT * FROM "bar" WHERE "id" IN (:wqp__1, :wqp__2, :wqp__3))
    AS "b" ON "b"."foo_id" = "a"."id"
    WHERE "a"."id" = :wqp__0
SQL
            ,
            (string) $q
        );

        self::assertSqlEquals(
            <<<SQL
SELECT "a".* FROM "foo" AS "a"
    LEFT JOIN
    (SELECT * FROM "bar" WHERE "id" IN (1, 2, 3))
    AS "b" ON "b"."foo_id" = "a"."id"
    WHERE "a"."id" = 123
SQL
            ,
            $q
        );
    }

    public function testFormat()
    {
        $result = $this->instance->format('SELECT %n FROM %n WHERE %n = %a', 'foo', '#__bar', 'id', 10);

        $sql = 'SELECT ' . $this->instance->quoteName('foo') . ' FROM ' . $this->instance->quoteName('#__bar') .
            ' WHERE ' . $this->instance->quoteName('id') . ' = 10';

        $this->assertEquals($sql, $result);

        $result = $this->instance->format(
            'SELECT %n FROM %n WHERE %n = %t OR %3$n = %Z',
            'id',
            '#__foo',
            'date',
            'nouse'
        );

        $sql = 'SELECT ' . $this->instance->quoteName('id') . ' FROM ' . $this->instance->quoteName('#__foo') .
            ' WHERE ' . $this->instance->quoteName('date') .
            ' = ' . $this->instance->getExpression()->currentTimestamp() .
            ' OR ' . $this->instance->quoteName('date') . ' = ' . $this->instance->quote($this->instance->nullDate());

        $this->assertEquals($sql, $result);
    }

    /**
     * @see  Query::quoteName
     */
    public function testQuoteName(): void
    {
        $this->assertEquals(self::replaceQn('"foo"'), $this->instance->quoteName('foo'));
        $this->assertEquals([self::replaceQn('"foo"')], $this->instance->qnMultiple(['foo']));
    }

    /**
     * @see  Query::quote
     */
    public function testQuote(): void
    {
        $q = new Query(
            $func = static function (string $value) {
                return addslashes($value);
            }
        );

        $s = $q->quote("These are Simon's items");

        self::assertEquals("'These are Simon\'s items'", $s);

        $q = new Query(
            $obj = new class {
                public function escape(string $value): string
                {
                    return addslashes($value);
                }
            }
        );

        $s = $q->quote("These are Simon's items");

        self::assertEquals("'These are Simon\'s items'", $s);
    }

    /**
     * @see  Query::escape
     */
    public function testEscape(): void
    {
        $q = new Query(
            $escaper = static function (string $value) {
                return addslashes($value);
            }
        );

        $s = $q->escape("These are Simon's items");

        self::assertEquals("These are Simon\'s items", $s);

        $q = new Query(
            $escaper = new class {
                public function escape(string $value): string
                {
                    return addslashes($value);
                }
            }
        );

        $s = $q->escape("These are Simon's items");

        self::assertEquals("These are Simon\'s items", $s);
    }

    public function testSuffix()
    {
        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->where('a', '<', 123)
            ->suffix('FOR UPDATE');

        self::assertSqlEquals(
            'SELECT * FROM "foo" WHERE "a" < 123 FOR UPDATE',
            $q
        );

        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->where('a', '<', 123)
            ->forUpdate();

        self::assertSqlEquals(
            'SELECT * FROM "foo" WHERE "a" < 123 FOR UPDATE',
            $q
        );

        $q = self::createQuery()
            ->select('*')
            ->from('foo')
            ->where('a', '<', 123)
            ->forShare('NOWAIT');

        self::assertSqlEquals(
            'SELECT * FROM "foo" WHERE "a" < 123 FOR SHARE NOWAIT',
            $q
        );
    }

    public function testSql()
    {
        $q = self::createQuery()
            ->sql('SELECT * FROM foo WHERE id = 5');

        self::assertSqlEquals(
            'SELECT * FROM foo WHERE id = 5',
            $q
        );
    }

    /**
     * Method to test clear().
     *
     * @return void
     */
    public function testClear()
    {
        $query = self::createQuery();

        $query->select('*')->from('foo');

        $query->clear();

        $this->assertNull($query->getSelect());
        $this->assertNull($query->getFrom());
    }

    /**
     * Method to test clear().
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testClearClause()
    {
        $q = self::createQuery();

        $clauses = [
            'from' => ['foo', 'asc', 'yoo', 'goo', 'hoo'],
            'join' => ['foo', 'asc', 'yoo', 'goo', 'hoo'],
            'set' => ['foo', 'asc', 'yoo', 'goo', 'hoo'],
            'where' => ['foo', 'asc', 'yoo', 'goo', 'hoo'],
            'group' => ['foo', 'asc', 'yoo', 'goo', 'hoo'],
            'having' => ['foo', 'asc', 'yoo', 'goo', 'hoo'],
            // Union should before order because it will clear order.
            'union' => ['foo', 'asc', 'yoo', 'goo', 'hoo'],
            'order' => ['foo', 'asc', 'yoo', 'goo', 'hoo'],
            'columns' => ['foo', 'asc', 'yoo', 'goo', 'hoo'],
            'values' => [['foo', 'asc'], ['yoo', 'goo']],
        ];

        // Set the clauses
        foreach ($clauses as $clause => $args) {
            $q->$clause(...$args);
        }

        // Test each clause.
        foreach (array_keys($clauses) as $clause) {
            $query = clone $q;

            // Clear the clause.
            $query->clear($clause);

            // Check that clause was cleared.
            $this->assertNull($query->{'get' . ucfirst($clause)}());

            // Check the state of the other clauses.
            foreach (array_keys($clauses) as $clause2) {
                if ($clause !== $clause2) {
                    $this->assertNotNull(
                        ReflectAccessor::getValue($query, $clause2),
                        $clause2 . ' Should not be NULL if we clear ' . $clause . '.'
                    );
                }
            }
        }
    }

    /**
     * Method to test clear().
     *
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testClearType()
    {
        $q = self::createQuery();

        $types = [
            'select',
            'delete',
            'update',
            'insert',
        ];

        $clauses = [
            'from' => ['foo', 'asc', 'yoo', 'goo', 'hoo'],
            'join' => ['foo', 'asc', 'yoo', 'goo', 'hoo'],
            'set' => ['foo', 'asc', 'yoo', 'goo', 'hoo'],
            'where' => ['foo', 'asc', 'yoo', 'goo', 'hoo'],
            'group' => ['foo', 'asc', 'yoo', 'goo', 'hoo'],
            'having' => ['foo', 'asc', 'yoo', 'goo', 'hoo'],
            'union' => ['foo', 'asc', 'yoo', 'goo', 'hoo'],
            'order' => ['foo', 'asc', 'yoo', 'goo', 'hoo'],
            'columns' => ['foo', 'asc', 'yoo', 'goo', 'hoo'],
            'values' => [['foo', 'asc'], ['yoo', 'goo']],
        ];

        // Set the clauses
        foreach ($clauses as $clause => $args) {
            $q->$clause(...$args);
        }

        // Check that all properties have been cleared
        foreach ($types as $type) {
            $query = clone $q;

            // Set the type.
            $query->$type('foo');

            // Clear the type.
            $query->clear($type);

            // Check the type has been cleared.
            $this->assertNull($query->getType(), 'Query property: ' . $type . ' should be null.');

            $this->assertNull($query->{'get' . ucfirst($type)}(), $type . ' should be null.');

            // Now check the claues have not been affected.
            foreach (array_keys($clauses) as $clause) {
                $this->assertNotNull(
                    ReflectAccessor::getValue($query, $clause),
                    $clause . ' should exists if we clear ' . $type
                );
            }
        }
    }

    public function testStripQuote()
    {
        $quoteStr = $this->instance->quote('foo');

        self::assertNotEquals('foo', $quoteStr);

        self::assertEquals(
            'foo',
            $this->instance->stripQuote($quoteStr)
        );
    }

    public function testStripNameQuote()
    {
        $quoteStr = $this->instance->quoteName('foo');

        self::assertNotEquals('foo', $quoteStr);

        self::assertEquals(
            'foo',
            $this->instance->stripNameQuote($quoteStr)
        );
    }

    public static function createQuery($conn = null): Query
    {
        $connection = $conn ?? (static::$escaper ??= new MockEscaper());

        return new Query($connection, static::createGrammar());
    }

    public static function createGrammar(): AbstractGrammar
    {
        return new BaseGrammar();
    }

    protected function setUp(): void
    {
        $this->instance = self::createQuery();
    }

    protected function tearDown(): void
    {
    }
}
